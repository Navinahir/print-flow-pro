<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\DTOs\Merchant\Pdf\PdfTempPath;
use App\Http\Controllers\Controller;
use App\Models\UploadJob;
use App\Services\Merchant\PickingList\PickingListSpreadsheetReader;
use App\Services\Merchant\Pdf\PdfTempStorageService;
use App\Services\Merchant\Upload\UploadSpreadsheetFileResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadSpreadsheetPreviewController extends Controller
{
    public function __construct(
        private readonly UploadSpreadsheetFileResolver $spreadsheetFileResolver,
        private readonly PickingListSpreadsheetReader $spreadsheetReader,
        private readonly PdfTempStorageService $tempStorage,
    ) {}

    public function __invoke(Request $request, UploadJob $upload, int $index): JsonResponse
    {
        $this->authorize('view', $upload);

        $file = $this->spreadsheetFileResolver->resolve($upload, $index);
        $absolutePath = $this->tempStorage->absolutePath(new PdfTempPath($file['disk'], $file['path']));

        if (! is_file($absolutePath)) {
            abort(404, __('merchant.uploads.errors.source_missing'));
        }

        $preview = $this->spreadsheetReader->readPreview($absolutePath, 20);

        return response()->json([
            'headers' => $preview['headers'],
            'rows' => $preview['rows'],
            'file_name' => $file['original_name'],
        ]);
    }
}
