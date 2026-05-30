<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\DTOs\Merchant\Preview\PickingListPreviewData;

class PickingListPreviewService
{
    public function buildSamplePreview(string $suffix = '1'): PickingListPreviewData
    {
        $rows = [
            [
                'sku' => 'SKU-1001',
                'name' => (string) __('merchant.printing.preview.picking_list.samples.item_one'),
                'location' => 'A-01-03',
                'quantity' => 2,
            ],
            [
                'sku' => 'SKU-2044',
                'name' => (string) __('merchant.printing.preview.picking_list.samples.item_two'),
                'location' => 'B-12-01',
                'quantity' => 1,
            ],
            [
                'sku' => 'SKU-3099',
                'name' => (string) __('merchant.printing.preview.picking_list.samples.item_three'),
                'location' => 'C-04-02',
                'quantity' => 3,
            ],
        ];

        return new PickingListPreviewData(
            listReference: (string) __('merchant.printing.preview.picking_list.samples.list_reference', ['id' => $suffix]),
            warehouse: (string) __('merchant.printing.preview.picking_list.samples.warehouse'),
            pickDate: (string) __('merchant.printing.preview.picking_list.samples.pick_date'),
            rows: $rows,
            totalUnits: array_sum(array_column($rows, 'quantity')),
        );
    }
}
