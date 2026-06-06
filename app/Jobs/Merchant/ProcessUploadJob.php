<?php

declare(strict_types=1);

namespace App\Jobs\Merchant;

use App\Actions\Merchant\Pdf\HandlePdfProcessingFailure;
use App\Actions\Merchant\Pdf\RunPdfProcessingPipeline;
use App\Actions\Merchant\Upload\CompleteUploadProcessing;
use App\Actions\Merchant\Upload\FailUploadProcessing;
use App\Actions\Merchant\Upload\MarkUploadProcessing;
use App\Models\UploadJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUploadJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public UploadJob $uploadJob,
    ) {}

    public function handle(
        MarkUploadProcessing $markProcessing,
        RunPdfProcessingPipeline $runPipeline,
        CompleteUploadProcessing $completeProcessing,
        FailUploadProcessing $failProcessing,
        HandlePdfProcessingFailure $handleFailure,
    ): void {
        $uploadJob = $this->uploadJob->fresh(['pdfUploads', 'merchant']);

        if ($uploadJob === null) {
            return;
        }

        $markProcessing->execute($uploadJob);

        $result = $runPipeline->execute($uploadJob);

        if ($result->success) {
            $completeProcessing->execute($uploadJob, $result);

            return;
        }

        $message = $result->message ?? __('merchant.pdf.errors.pipeline_failed', ['detail' => '']);
        $handleFailure->execute($uploadJob, $result);
        $failProcessing->execute($uploadJob, $message, $result->errors);
    }
}
