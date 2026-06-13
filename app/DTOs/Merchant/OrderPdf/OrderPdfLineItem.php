<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\OrderPdf;

final readonly class OrderPdfLineItem
{
    public function __construct(
        public int $lineNumber,
        public string $mainSku,
        public string $productName,
        public string $variantSku,
        public string $variantName,
        public int $quantity,
        public int $lineTotal,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'line_number' => $this->lineNumber,
            'main_sku' => $this->mainSku,
            'product_name' => $this->productName,
            'variant_sku' => $this->variantSku,
            'variant_name' => $this->variantName,
            'quantity' => $this->quantity,
            'line_total' => $this->lineTotal,
        ];
    }
}
