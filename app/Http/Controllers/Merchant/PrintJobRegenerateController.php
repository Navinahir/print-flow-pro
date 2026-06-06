<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Actions\Merchant\Upload\RegeneratePrintJobProcessing;
use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use App\Models\UploadJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrintJobRegenerateController extends Controller
{
    public function __invoke(
        Request $request,
        UploadJob $upload,
        PrintJob $printJob,
        RegeneratePrintJobProcessing $regenerate,
    ): JsonResponse {
        if ((int) $printJob->upload_job_id !== (int) $upload->id) {
            abort(404);
        }

        $this->authorize('regenerate', $printJob);

        $output = $regenerate->execute($printJob);

        return response()->json([
            'message' => __('merchant.uploads.detail.regenerate_success'),
            'output' => $output,
        ]);
    }
}
