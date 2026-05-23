<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\UploadJob;
use App\Services\AuditLogService;

class UploadJobObserver
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function created(UploadJob $uploadJob): void
    {
        $this->auditLogService->logUpload(
            event: 'upload_job.created',
            description: "Upload job #{$uploadJob->id} created.",
            auditable: $uploadJob,
            merchant: $uploadJob->merchant,
            properties: [
                'type' => $uploadJob->type?->value,
                'status' => $uploadJob->status?->value,
            ],
        );
    }

    public function updated(UploadJob $uploadJob): void
    {
        if (! $uploadJob->wasChanged('status')) {
            return;
        }

        $this->auditLogService->logUpload(
            event: 'upload_job.status_changed',
            description: "Upload job #{$uploadJob->id} status changed to {$uploadJob->status?->value}.",
            auditable: $uploadJob,
            merchant: $uploadJob->merchant,
            properties: [
                'from' => $uploadJob->getRawOriginal('status'),
                'to' => $uploadJob->status?->value,
            ],
        );
    }
}
