<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Upload;

use App\Enums\PdfProcessingMode;
use App\Jobs\Merchant\ProcessUploadJob;
use App\Models\UploadJob;

class DispatchUploadProcessing
{
    public function execute(UploadJob $uploadJob): void
    {
        if (! $this->shouldDispatch($uploadJob)) {
            return;
        }

        ProcessUploadJob::dispatch($uploadJob);
    }

    private function shouldDispatch(UploadJob $uploadJob): bool
    {
        $mode = PdfProcessingMode::fromUploadJobType($uploadJob->type);
        $modeConfig = config('pdf.modes.'.$mode->configKey());

        if (! is_array($modeConfig) || ! ($modeConfig['enabled'] ?? false)) {
            return false;
        }

        $processor = $modeConfig['processor'] ?? null;

        return is_string($processor) && class_exists($processor);
    }
}
