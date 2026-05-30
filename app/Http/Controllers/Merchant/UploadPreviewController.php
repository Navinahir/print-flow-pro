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

        $result = $this->uploadPreviewService->resolve($upload);

        if (! $result->available || $result->preview === null) {
            return response()->json([
                'message' => __('merchant.uploads.preview.unavailable'),
            ], 404);
        }

        return response()->json($result->toArray());
    }
}
