<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams normalized PDF inline for browser preview (does not mark as downloaded).
 */
class PrintJobPreviewController extends Controller
{
    public function __invoke(Request $request, PrintJob $printJob): StreamedResponse
    {
        $this->authorize('download', $printJob);

        $disk = Storage::disk($printJob->output_disk);

        if ($printJob->output_path === null || ! $disk->exists($printJob->output_path)) {
            abort(404, __('merchant.print_jobs.errors.file_missing'));
        }

        return $disk->response(
            $printJob->output_path,
            'preview.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ],
        );
    }
}
