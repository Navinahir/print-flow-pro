<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfProcessingResult;
use App\Models\Merchant;
use App\Models\UploadJob;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;

/**
 * Records PDF processing failures and persists upload job error details.
 */
class HandlePdfProcessingFailure
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function execute(UploadJob $uploadJob, PdfProcessingResult $result): UploadJob
    {
        $message = $result->message ?? __('merchant.pdf.errors.pipeline_failed', ['detail' => '']);

        Log::warning('PDF processing failed', [
            'upload_job_id' => $uploadJob->id,
            'merchant_id' => $uploadJob->merchant_id,
            'errors' => $result->errors,
            'validation' => $result->validation?->toArray(),
        ]);

        $merchant = $uploadJob->merchant ?? Merchant::query()->find($uploadJob->merchant_id);

        if ($merchant instanceof Merchant) {
            $this->auditLogService->logUpload(
                event: 'upload.pdf_processing_failed',
                description: "PDF processing failed for upload job #{$uploadJob->id}.",
                auditable: $uploadJob,
                merchant: $merchant,
                properties: [
                    'message' => $message,
                    'errors' => $result->errors,
                    'status' => $result->status->value,
                    'validation_codes' => array_map(
                        static fn ($code) => $code->value,
                        $result->validation?->codes ?? [],
                    ),
                ],
            );
        }

        return $uploadJob;
    }
}
