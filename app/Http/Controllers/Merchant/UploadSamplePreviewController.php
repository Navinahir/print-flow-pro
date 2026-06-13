<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Services\Merchant\PickingList\PickingListSpreadsheetReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadSamplePreviewController extends Controller
{
    public function __construct(
        private readonly PickingListSpreadsheetReader $spreadsheetReader,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $path = trim((string) $request->query('path', ''));

        if ($path === '' || ! $this->isAllowedSamplePath($path)) {
            abort(404);
        }

        $absolutePath = public_path($path);

        if (! is_file($absolutePath)) {
            abort(404);
        }

        $preview = $this->spreadsheetReader->readPreview($absolutePath, 20);

        return response()->json([
            'headers' => $preview['headers'],
            'rows' => $preview['rows'],
        ]);
    }

    private function isAllowedSamplePath(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);

        if (str_contains($normalized, '..')) {
            return false;
        }

        return str_starts_with($normalized, 'samples/');
    }
}
