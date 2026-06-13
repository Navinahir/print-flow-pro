<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\UploadJob;
use App\Services\Merchant\Pdf\PdfTempStorageService;
use App\Services\Merchant\Upload\UploadSpreadsheetFileResolver;
use App\DTOs\Merchant\Pdf\PdfTempPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadSpreadsheetDownloadController extends Controller
{
    public function __construct(
        private readonly UploadSpreadsheetFileResolver $spreadsheetFileResolver,
        private readonly PdfTempStorageService $tempStorage,
    ) {}

    public function __invoke(Request $request, UploadJob $upload, int $index): StreamedResponse
    {
        $this->authorize('view', $upload);

        $file = $this->spreadsheetFileResolver->resolve($upload, $index);
        $disk = Storage::disk($file['disk']);

        if (! $disk->exists($file['path'])) {
            abort(404, __('merchant.uploads.errors.source_missing'));
        }

        return $disk->download($file['path'], $file['original_name']);
    }
}
