<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Upload;

use App\Enums\UploadStatus;
use App\Models\Merchant;
use App\Models\UploadJob;
use App\Services\AuditLogService;

class FailUploadProcessing
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  list<string>  $errors
     */
    public function execute(UploadJob $uploadJob, string $message, array $errors = []): UploadJob
    {
        $uploadJob->update([
            'status' => UploadStatus::Failed,
            'error_message' => $message,
            'completed_at' => now(),
        ]);

        $merchant = $uploadJob->merchant ?? Merchant::query()->find($uploadJob->merchant_id);

        if ($merchant instanceof Merchant) {
            $this->auditLogService->logUpload(
                event: 'upload.processing_failed',
                description: "Upload job #{$uploadJob->id} processing failed.",
                auditable: $uploadJob,
                merchant: $merchant,
                properties: [
                    'message' => $message,
                    'errors' => $errors,
                ],
            );
        }

        return $uploadJob->fresh();
    }
}
