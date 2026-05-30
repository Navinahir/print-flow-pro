<?php

declare(strict_types=1);

use App\Http\Controllers\Merchant\Printing\AspectRatioValidationController;
use App\Http\Controllers\Merchant\Printing\DeliveryLabelCsvUploadController;
use App\Http\Controllers\Merchant\Printing\DeliveryLabelsController;
use App\Http\Controllers\Merchant\Printing\LogisticsLabelsController;
use App\Http\Controllers\Merchant\Printing\OrderDetailsController;
use App\Http\Controllers\Merchant\Printing\PickingListController;
use App\Http\Controllers\Merchant\Printing\PrintingPreviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('printing')
    ->name('printing.')
    ->group(function (): void {
        Route::post('aspect-ratio/validate', [AspectRatioValidationController::class, 'store'])
            ->name('aspect_ratio.validate');

        Route::post('preview', [PrintingPreviewController::class, 'show'])
            ->name('preview.show');

        Route::post('delivery-labels/csv', [DeliveryLabelCsvUploadController::class, 'store'])
            ->middleware('printing.module:delivery_labels')
            ->name('delivery_labels.csv.store');

        Route::get('order-details', [OrderDetailsController::class, 'index'])
            ->middleware('printing.module:order_details')
            ->name('order_details.index');

        Route::get('logistics-labels', [LogisticsLabelsController::class, 'index'])
            ->middleware('printing.module:logistics_labels')
            ->name('logistics_labels.index');

        Route::get('picking-list', [PickingListController::class, 'index'])
            ->middleware('printing.module:picking_list')
            ->name('picking_list.index');

        Route::get('delivery-labels', [DeliveryLabelsController::class, 'index'])
            ->middleware('printing.module:delivery_labels')
            ->name('delivery_labels.index');
    });
