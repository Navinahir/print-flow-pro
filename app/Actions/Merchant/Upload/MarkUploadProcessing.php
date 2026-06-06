<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Upload;

use App\Enums\UploadStatus;
use App\Models\UploadJob;

class MarkUploadProcessing
{
    public function execute(UploadJob $uploadJob): UploadJob
    {
        $uploadJob->update([
            'status' => UploadStatus::Processing,
            'started_at' => now(),
            'error_message' => null,
        ]);

        return $uploadJob->fresh();
    }
}
