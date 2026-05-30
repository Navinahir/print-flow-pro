<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\DTOs\Merchant\Preview\OrderDetailsPreviewData;

class OrderDetailsPreviewService
{
    public function buildSamplePreview(string $suffix = '1'): OrderDetailsPreviewData
    {
        return new OrderDetailsPreviewData(
            orderNumber: (string) __('merchant.printing.preview.order_details.samples.order_number', ['id' => $suffix]),
            customerName: (string) __('merchant.printing.preview.order_details.samples.customer_name'),
            orderDate: (string) __('merchant.printing.preview.order_details.samples.order_date'),
            status: (string) __('merchant.printing.preview.order_details.samples.status'),
            lineItems: [
                [
                    'sku' => 'SKU-1001',
                    'name' => (string) __('merchant.printing.preview.order_details.samples.item_one'),
                    'qty' => 2,
                    'price' => 'NT$ 598',
                ],
                [
                    'sku' => 'SKU-2044',
                    'name' => (string) __('merchant.printing.preview.order_details.samples.item_two'),
                    'qty' => 1,
                    'price' => 'NT$ 320',
                ],
            ],
            summary: [
                'subtotal' => 'NT$ 1,516',
                'shipping' => 'NT$ 60',
                'total' => 'NT$ 1,576',
                'currency' => 'TWD',
            ],
            notes: (string) __('merchant.printing.preview.order_details.samples.notes'),
        );
    }
}
