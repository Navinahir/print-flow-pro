<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\Actions\Merchant\Upload\DispatchUploadProcessing;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\Merchant;
use App\Models\PdfUpload;
use App\Models\UploadJob;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UploadService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly DispatchUploadProcessing $dispatchUploadProcessing,
    ) {}

    /**
     * @param  list<UploadedFile>  $files
     * @param  bool|null  $thermalCombinedOutput  When false and multiple PDFs, output one file per upload.
     * @param  bool|null  $pickingCombinedOutput  When false and multiple spreadsheets, output one PDF per upload.
     * @param  bool|null  $orderCombinedOutput  When false and multiple spreadsheets, output one PDF per upload.
     */
    public function createJob(
        User $user,
        Merchant $merchant,
        UploadJobType $type,
        array $files,
        ?bool $thermalCombinedOutput = null,
        ?bool $pickingCombinedOutput = null,
        ?bool $orderCombinedOutput = null,
    ): UploadJob {
        return DB::transaction(function () use ($user, $merchant, $type, $files, $thermalCombinedOutput, $pickingCombinedOutput, $orderCombinedOutput): UploadJob {
            $fileCount = count($files);
            $metadata = [
                'original_names' => array_map(
                    static fn (UploadedFile $file): string => $file->getClientOriginalName(),
                    $files,
                ),
            ];

            if ($type === UploadJobType::ThermalLabel && $fileCount > 1) {
                $metadata['thermal_output_mode'] = ($thermalCombinedOutput ?? true)
                    ? 'combined'
                    : 'separate';
            }

            if ($type === UploadJobType::PickingList && $fileCount > 1) {
                $metadata['picking_output_mode'] = ($pickingCombinedOutput ?? true)
                    ? 'combined'
                    : 'separate';
            }

            if ($type === UploadJobType::OrderPdf && $fileCount > 1) {
                $metadata['order_output_mode'] = ($orderCombinedOutput ?? true)
                    ? 'combined'
                    : 'separate';
            }

            $job = UploadJob::query()->create([
                'merchant_id' => $merchant->id,
                'country_code' => $merchant->country_code,
                'user_id' => $user->id,
                'uploaded_by' => $user->id,
                'type' => $type,
                'status' => UploadStatus::Pending,
                'file_count' => $fileCount,
                'metadata' => $metadata,
            ]);

            foreach ($files as $file) {
                $this->storeFile($job, $merchant, $file, $type);
            }

            $this->auditLogService->logUpload(
                event: 'upload.received',
                description: "Upload job #{$job->id} received ({$type->value}).",
                auditable: $job,
                merchant: $merchant,
                properties: ['file_count' => count($files)],
            );

            $this->dispatchUploadProcessing->execute($job);

            return $job->fresh(['pdfUploads', 'uploadedBy']);
        });
    }

    private function storeFile(
        UploadJob $job,
        Merchant $merchant,
        UploadedFile $file,
        UploadJobType $type,
    ): ?PdfUpload {
        $extension = strtolower($file->getClientOriginalExtension());
        $storedName = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs(
            "merchants/{$merchant->id}/jobs/{$job->id}",
            $storedName,
            'temp',
        );

        if ($path === false) {
            return null;
        }

        if (in_array($extension, ['pdf'], true)) {
            return PdfUpload::query()->create([
                'merchant_id' => $merchant->id,
                'country_code' => $merchant->country_code,
                'upload_job_id' => $job->id,
                'original_name' => $file->getClientOriginalName(),
                'disk' => 'temp',
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize() ?: 0,
                'status' => UploadStatus::Pending,
                'checksum' => hash_file('sha256', $file->getRealPath()) ?: null,
            ]);
        }

        $metadata = $job->metadata ?? [];
        $files = $metadata['spreadsheet_files'] ?? [];
        $files[] = [
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'temp',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize() ?: 0,
        ];
        $metadata['spreadsheet_files'] = $files;
        $job->update(['metadata' => $metadata]);

        return null;
    }
}
