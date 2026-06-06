<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Enums\PrintJobStatus;
use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrintJobDownloadController extends Controller
{
    public function __invoke(Request $request, PrintJob $printJob): StreamedResponse
    {
        $this->authorize('download', $printJob);

        $disk = Storage::disk($printJob->output_disk);

        if ($printJob->output_path === null || ! $disk->exists($printJob->output_path)) {
            abort(404, __('merchant.print_jobs.errors.file_missing'));
        }

        if ($printJob->expires_at !== null && $printJob->expires_at->isPast()) {
            abort(410, __('merchant.print_jobs.errors.expired'));
        }

        $sheetNumber = (int) ($printJob->metadata['sheet_number'] ?? $printJob->source_page_number);
        $originalName = (string) ($printJob->metadata['original_name'] ?? 'label.pdf');
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $downloadName = "{$basename}-A4-sheet{$sheetNumber}.pdf";

        $printJob->update([
            'status' => PrintJobStatus::Downloaded,
            'downloaded_at' => now(),
        ]);

        return $disk->download($printJob->output_path, $downloadName);
    }
}
