<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

final readonly class ShopeeOrderPdfParseResult
{
    /**
     * @param  list<ParsedShopeeOrder>  $orders
     * @param  list<array{sku: string, name: string, qty: int, price: string}>  $lineItems
     */
    public function __construct(
        public array $orders,
        public array $lineItems,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'orders' => array_map(static fn (ParsedShopeeOrder $order): array => $order->toArray(), $this->orders),
            'line_items' => $this->lineItems,
        ];
    }

    public static function empty(): self
    {
        return new self([], []);
    }
}
