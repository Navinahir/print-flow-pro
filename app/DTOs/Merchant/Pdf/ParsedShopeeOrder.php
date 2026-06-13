<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

final readonly class ParsedShopeeOrder
{
    /**
     * @param  list<array{sku: string, name: string, qty: int, price: string}>  $lineItems
     */
    public function __construct(
        public ?string $orderNumber,
        public array $lineItems,
        public ?string $buyerNote = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order_number' => $this->orderNumber,
            'line_items' => $this->lineItems,
            'buyer_note' => $this->buyerNote,
        ];
    }
}
