<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Upload;

use App\Actions\Merchant\Pdf\PreparePdfProcessingContext;
use App\Enums\PrintJobStatus;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\PrintJob;
use App\Services\AuditLogService;
use App\Services\Merchant\Pdf\Processors\LogisticsLabelsProcessor;
use App\Services\Merchant\UploadShowViewService;
use Illuminate\Validation\ValidationException;

class RegeneratePrintJobProcessing
{
    public function __construct(
        private readonly PreparePdfProcessingContext $prepareContext,
        private readonly LogisticsLabelsProcessor $logisticsLabelsProcessor,
        private readonly UploadShowViewService $uploadShowViewService,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(PrintJob $printJob): array
    {
        $printJob->loadMissing(['uploadJob.pdfUploads', 'uploadJob.merchant']);

        $uploadJob = $printJob->uploadJob;

        if ($uploadJob === null) {
            throw ValidationException::withMessages([
                'print_job' => __('merchant.uploads.errors.regenerate_missing_sources'),
            ]);
        }

        if ($uploadJob->type !== UploadJobType::ThermalLabel) {
            throw ValidationException::withMessages([
                'print_job' => __('merchant.uploads.errors.regenerate_unsupported'),
            ]);
        }

        if ($uploadJob->status !== UploadStatus::Completed) {
            throw ValidationException::withMessages([
                'print_job' => __('merchant.uploads.errors.regenerate_not_ready'),
            ]);
        }

        if (! in_array($printJob->status, [PrintJobStatus::Ready, PrintJobStatus::Downloaded], true)) {
            throw ValidationException::withMessages([
                'print_job' => __('merchant.uploads.errors.regenerate_not_ready'),
            ]);
        }

        $context = $this->prepareContext->execute($uploadJob);
        $regenerated = $this->logisticsLabelsProcessor->regeneratePrintJob($printJob, $context);

        $merchant = $uploadJob->merchant;
        $uploadJob->loadMissing('printJobs');

        if ($merchant !== null) {
            $this->auditLogService->logUpload(
                event: 'upload.print_job_regenerated',
                description: "Print job #{$regenerated->id} regenerated for upload #{$uploadJob->id}.",
                auditable: $uploadJob,
                merchant: $merchant,
                properties: ['print_job_id' => $regenerated->id],
            );
        }

        return $this->uploadShowViewService->formatPrintOutput(
            $regenerated,
            $uploadJob,
            $uploadJob->printJobs,
        );
    }
}
