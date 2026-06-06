<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Upload;

use App\Enums\UploadJobType;
use App\Jobs\Merchant\ProcessUploadJob;
use App\Models\UploadJob;

class DispatchUploadProcessing
{
    public function execute(UploadJob $uploadJob): void
    {
        if ($uploadJob->type !== UploadJobType::ThermalLabel) {
            return;
        }

        ProcessUploadJob::dispatch($uploadJob);
    }
}
