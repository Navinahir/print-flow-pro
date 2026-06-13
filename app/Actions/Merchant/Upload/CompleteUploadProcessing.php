<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Upload;

use App\DTOs\Merchant\Pdf\PdfProcessingResult;
use App\Enums\UploadStatus;
use App\Models\Merchant;
use App\Models\UploadJob;
use App\Services\AuditLogService;

class CompleteUploadProcessing
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function execute(UploadJob $uploadJob, PdfProcessingResult $result): UploadJob
    {
        $uploadJob->refresh();

        $fileErrors = is_array($result->normalization?->metadata['file_errors'] ?? null)
            ? $result->normalization->metadata['file_errors']
            : [];
        $partialSuccess = (bool) ($result->normalization?->metadata['partial_success'] ?? false)
            && $fileErrors !== [];

        $metadata = $uploadJob->metadata ?? [];
        $metadata['processing'] = [
            'completed_at' => now()->toIso8601String(),
            'print_job_ids' => $result->normalization?->metadata['print_job_ids'] ?? [],
            'processed_pages' => $result->normalization?->metadata['processed_pages'] ?? 0,
        ];

        if ($fileErrors !== []) {
            $metadata['file_errors'] = $fileErrors;
        }

        $uploadJob->update([
            'status' => $partialSuccess ? UploadStatus::CompletedWithErrors : UploadStatus::Completed,
            'completed_at' => now(),
            'error_message' => $partialSuccess
                ? __('merchant.uploads.errors.partial_processing_summary', ['count' => count($fileErrors)])
                : null,
            'metadata' => $metadata,
        ]);

        $merchant = $uploadJob->merchant ?? Merchant::query()->find($uploadJob->merchant_id);

        if ($merchant instanceof Merchant) {
            $this->auditLogService->logUpload(
                event: 'upload.processing_completed',
                description: "Upload job #{$uploadJob->id} processing completed.",
                auditable: $uploadJob,
                merchant: $merchant,
                properties: [
                    'processed_pages' => $metadata['processing']['processed_pages'],
                    'message' => $result->message,
                ],
            );
        }

        return $uploadJob->fresh(['printJobs', 'pdfUploads']);
    }
}
