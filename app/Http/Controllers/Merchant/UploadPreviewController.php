<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\UploadJob;
use App\Services\Merchant\UploadPreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadPreviewController extends Controller
{
    public function __construct(
        private readonly UploadPreviewService $uploadPreviewService,
    ) {}

    public function show(Request $request, UploadJob $upload): JsonResponse
    {
        $this->authorize('view', $upload);

        $itemId = $request->string('item_id')->toString();
        $itemId = $itemId !== '' ? $itemId : null;

        $result = $this->uploadPreviewService->resolve($upload, $itemId);

        if (! $result->available || $result->preview === null) {
            return response()->json([
                'message' => $result->statusMessage ?? __('merchant.uploads.preview.unavailable'),
                'available' => false,
                'status_message' => $result->statusMessage,
            ], $result->statusMessage !== null ? 200 : 404);
        }

        return response()->json($result->toArray());
    }
}
