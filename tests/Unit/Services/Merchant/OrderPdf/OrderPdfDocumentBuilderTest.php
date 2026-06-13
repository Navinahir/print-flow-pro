<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\OrderPdf;

use App\DTOs\Merchant\PickingList\PickingListRow;
use App\Services\Merchant\OrderPdf\OrderPdfDocumentBuilder;
use Tests\TestCase;

class OrderPdfDocumentBuilderTest extends TestCase
{
    public function test_groups_rows_by_order_sn_within_a_single_source_file(): void
    {
        $builder = app(OrderPdfDocumentBuilder::class);

        $document = $builder->build([
            $this->makeRow('file-a.xlsx', 'ORDER-1', 'SKU-A'),
            $this->makeRow('file-a.xlsx', 'ORDER-1', 'SKU-B'),
            $this->makeRow('file-a.xlsx', 'ORDER-2', 'SKU-C'),
        ], ['file-a.xlsx']);

        $this->assertCount(2, $document->orders);
        $this->assertSame('ORDER-1', $document->orders[0]->orderSn);
        $this->assertCount(2, $document->orders[0]->lineItems);
        $this->assertSame('ORDER-2', $document->orders[1]->orderSn);
    }

    public function test_preserves_duplicate_order_numbers_from_different_source_files(): void
    {
        $builder = app(OrderPdfDocumentBuilder::class);

        $document = $builder->build([
            $this->makeRow('original.xlsx', 'ORDER-1', 'SKU-A'),
            $this->makeRow('original.xlsx', 'ORDER-2', 'SKU-B'),
            $this->makeRow('copy.xlsx', 'ORDER-1', 'SKU-A'),
            $this->makeRow('copy.xlsx', 'ORDER-2', 'SKU-B'),
        ], ['original.xlsx', 'copy.xlsx']);

        $this->assertCount(4, $document->orders);
        $this->assertSame('ORDER-1', $document->orders[0]->orderSn);
        $this->assertSame('ORDER-2', $document->orders[1]->orderSn);
        $this->assertSame('ORDER-1', $document->orders[2]->orderSn);
        $this->assertSame('ORDER-2', $document->orders[3]->orderSn);
    }

    private function makeRow(string $sourceFileName, string $orderSn, string $sku): PickingListRow
    {
        return new PickingListRow(
            lineNumber: 1,
            trackingNumber: 'TRACK-'.$orderSn,
            orderSn: $orderSn,
            mainSku: $sku,
            productName: 'Product '.$sku,
            variantSku: 'VAR-'.$sku,
            variantName: 'Variant '.$sku,
            quantity: 1,
            remarkFromBuyer: '',
            sellerNote: '',
            sourceFileName: $sourceFileName,
            unitPrice: 100,
        );
    }
}
