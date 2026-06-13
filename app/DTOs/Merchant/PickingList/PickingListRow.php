<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\PickingList;

final readonly class PickingListRow
{
    public function __construct(
        public int $lineNumber,
        public string $trackingNumber,
        public string $orderSn,
        public string $mainSku,
        public string $productName,
        public string $variantSku,
        public string $variantName,
        public int $quantity,
        public string $remarkFromBuyer,
        public string $sellerNote,
        public string $sourceFileName,
        public int $unitPrice = 0,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'line_number' => $this->lineNumber,
            'tracking_number' => $this->trackingNumber,
            'order_sn' => $this->orderSn,
            'main_sku' => $this->mainSku,
            'product_name' => $this->productName,
            'variant_sku' => $this->variantSku,
            'variant_name' => $this->variantName,
            'quantity' => $this->quantity,
            'remark_from_buyer' => $this->remarkFromBuyer,
            'seller_note' => $this->sellerNote,
            'source_file_name' => $this->sourceFileName,
            'unit_price' => $this->unitPrice,
        ];
    }
}
