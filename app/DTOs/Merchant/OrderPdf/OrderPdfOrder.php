<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\OrderPdf;

final readonly class OrderPdfOrder
{
    /**
     * @param  list<OrderPdfLineItem>  $lineItems
     */
    public function __construct(
        public string $orderSn,
        public int $packageNumber,
        public string $buyerNote,
        public array $lineItems,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order_sn' => $this->orderSn,
            'package_number' => $this->packageNumber,
            'buyer_note' => $this->buyerNote,
            'line_items' => array_map(static fn (OrderPdfLineItem $item): array => $item->toArray(), $this->lineItems),
        ];
    }
}
