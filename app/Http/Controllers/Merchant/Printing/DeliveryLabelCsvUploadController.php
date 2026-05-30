<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\Printing\StoreDeliveryLabelCsvRequest;
use App\Services\Merchant\Printing\DeliveryLabels\DeliveryLabelCsvImportService;
use Illuminate\Http\JsonResponse;

class DeliveryLabelCsvUploadController extends Controller
{
    public function __construct(
        private readonly DeliveryLabelCsvImportService $csvImportService,
    ) {}

    public function store(StoreDeliveryLabelCsvRequest $request): JsonResponse
    {
        $user = $request->user();
        $merchant = $user?->merchant;

        if ($merchant === null) {
            return response()->json([
                'message' => __('merchant.uploads.errors.no_merchant_profile'),
            ], 422);
        }

        $result = $this->csvImportService->import(
            $user,
            $merchant,
            $request->file('file'),
        );

        return response()->json($result->toArray());
    }
}
