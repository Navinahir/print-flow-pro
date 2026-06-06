<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Upload;

use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\UploadJob;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RegenerateUploadProcessing
{
    public function __construct(
        private readonly DispatchUploadProcessing $dispatchUploadProcessing,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function execute(UploadJob $uploadJob): UploadJob
    {
        if ($uploadJob->type !== UploadJobType::ThermalLabel) {
            throw ValidationException::withMessages([
                'upload' => __('merchant.uploads.errors.regenerate_unsupported'),
            ]);
        }

        if (! in_array($uploadJob->status, [UploadStatus::Completed, UploadStatus::Failed], true)) {
            throw ValidationException::withMessages([
                'upload' => __('merchant.uploads.errors.regenerate_not_ready'),
            ]);
        }

        if ($uploadJob->pdfUploads()->count() === 0) {
            throw ValidationException::withMessages([
                'upload' => __('merchant.uploads.errors.regenerate_missing_sources'),
            ]);
        }

        return DB::transaction(function () use ($uploadJob): UploadJob {
            $uploadJob->loadMissing(['printJobs', 'merchant', 'pdfUploads']);

            foreach ($uploadJob->printJobs as $printJob) {
                if (is_string($printJob->output_path) && $printJob->output_path !== '') {
                    Storage::disk($printJob->output_disk)->delete($printJob->output_path);
                }

                $printJob->delete();
            }

            $metadata = $uploadJob->metadata ?? [];
            unset($metadata['processing']);

            $uploadJob->update([
                'status' => UploadStatus::Pending,
                'error_message' => null,
                'completed_at' => null,
                'metadata' => $metadata,
            ]);

            $merchant = $uploadJob->merchant;

            if ($merchant !== null) {
                $this->auditLogService->logUpload(
                    event: 'upload.regenerate_requested',
                    description: "Upload job #{$uploadJob->id} regeneration requested.",
                    auditable: $uploadJob,
                    merchant: $merchant,
                );
            }

            $this->dispatchUploadProcessing->execute($uploadJob);

            return $uploadJob->fresh(['pdfUploads', 'printJobs', 'uploadedBy']);
        });
    }
}
