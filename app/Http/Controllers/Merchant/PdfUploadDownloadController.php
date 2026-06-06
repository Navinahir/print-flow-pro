<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\PdfUpload;
use App\Models\UploadJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfUploadDownloadController extends Controller
{
    public function __invoke(Request $request, UploadJob $upload, PdfUpload $pdfUpload): StreamedResponse
    {
        $this->authorize('view', $upload);
        $this->assertBelongsToJob($upload, $pdfUpload);

        $disk = Storage::disk($pdfUpload->disk);

        if (! $disk->exists($pdfUpload->path)) {
            abort(404, __('merchant.uploads.errors.source_missing'));
        }

        return $disk->download($pdfUpload->path, $pdfUpload->original_name);
    }

    private function assertBelongsToJob(UploadJob $upload, PdfUpload $pdfUpload): void
    {
        if ((int) $pdfUpload->upload_job_id !== (int) $upload->id) {
            abort(404);
        }
    }
}
