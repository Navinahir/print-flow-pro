<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\DTOs\Merchant\Preview\PickingListPreviewData;
use App\Models\PrintJob;

class PickingListPreviewService
{
    public function buildSamplePreview(string $suffix = '1'): PickingListPreviewData
    {
        return $this->buildFromRows(
            listReference: (string) __('merchant.printing.preview.picking_list.samples.list_reference', ['id' => $suffix]),
            warehouse: (string) __('merchant.printing.preview.picking_list.samples.warehouse'),
            pickDate: (string) __('merchant.printing.preview.picking_list.samples.pick_date'),
            rows: [
                [
                    'line_number' => 1,
                    'order_sn' => '260520V1D78XNC',
                    'product_name' => (string) __('merchant.printing.preview.picking_list.samples.item_one'),
                    'variant_name' => 'KA-38R64L,24V',
                    'variant_sku' => 'KA-38R64L,24V',
                    'quantity' => 1,
                ],
                [
                    'line_number' => 2,
                    'order_sn' => '260520V4NPV6EJ',
                    'product_name' => (string) __('merchant.printing.preview.picking_list.samples.item_two'),
                    'variant_name' => '',
                    'variant_sku' => '',
                    'quantity' => 1,
                ],
                [
                    'line_number' => 3,
                    'order_sn' => '2605210RV0VU5E',
                    'product_name' => (string) __('merchant.printing.preview.picking_list.samples.item_three'),
                    'variant_name' => 'G1a：30天180G_6G/天.可接聽',
                    'variant_sku' => '',
                    'quantity' => 1,
                ],
            ],
        );
    }

    public function buildFromPrintJob(PrintJob $printJob): PickingListPreviewData
    {
        $document = is_array($printJob->metadata['document'] ?? null)
            ? $printJob->metadata['document']
            : [];

        $rows = is_array($document['rows'] ?? null) ? $document['rows'] : [];
        $originalName = (string) ($printJob->metadata['original_name'] ?? __('merchant.printing.preview.picking_list.samples.list_title', ['id' => $printJob->id]));

        return $this->buildFromRows(
            listReference: $originalName,
            warehouse: (string) ($document['account_name'] ?? __('merchant.printing.preview.picking_list.samples.warehouse')),
            pickDate: (string) ($document['generated_at'] ?? now()->format('Y-m-d H:i')),
            rows: $rows,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function buildFromRows(string $listReference, string $warehouse, string $pickDate, array $rows): PickingListPreviewData
    {
        $normalizedRows = array_map(static function (array $row): array {
            return [
                'line_number' => (int) ($row['line_number'] ?? 0),
                'order_sn' => (string) ($row['order_sn'] ?? ''),
                'product_name' => (string) ($row['product_name'] ?? ''),
                'variant_name' => (string) ($row['variant_name'] ?? ''),
                'variant_sku' => (string) ($row['variant_sku'] ?? ''),
                'quantity' => (int) ($row['quantity'] ?? 0),
            ];
        }, $rows);

        $totalUnits = array_sum(array_map(static fn (array $row): int => (int) ($row['quantity'] ?? 0), $normalizedRows));

        return new PickingListPreviewData(
            listReference: $listReference,
            warehouse: $warehouse,
            pickDate: $pickDate,
            rows: $normalizedRows,
            totalUnits: $totalUnits,
        );
    }
}
