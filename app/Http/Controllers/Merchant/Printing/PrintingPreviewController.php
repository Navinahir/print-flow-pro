<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Enums\PrintingModule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\Printing\FetchPrintingPreviewRequest;
use App\Services\Merchant\Preview\PrintingPreviewResolver;
use Illuminate\Http\JsonResponse;

class PrintingPreviewController extends Controller
{
    public function __construct(
        private readonly PrintingPreviewResolver $previewResolver,
    ) {}

    public function show(FetchPrintingPreviewRequest $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $module = PrintingModule::from((string) $request->validated('module'));
        $preview = $this->previewResolver->resolve(
            $module,
            (string) $request->validated('item_id'),
            $user,
        );

        if ($preview === null) {
            return response()->json([
                'message' => __('merchant.printing.preview.not_found'),
            ], 404);
        }

        return response()->json([
            'preview' => $preview,
        ]);
    }
}
