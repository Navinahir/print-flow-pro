<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\OrderPdf;

final readonly class OrderPdfDocument
{
    /**
     * @param  list<OrderPdfOrder>  $orders
     * @param  list<string>  $sourceFiles
     */
    public function __construct(
        public array $orders,
        public array $sourceFiles,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'orders' => array_map(static fn (OrderPdfOrder $order): array => $order->toArray(), $this->orders),
            'source_files' => $this->sourceFiles,
            'order_count' => count($this->orders),
            'line_item_count' => array_sum(array_map(
                static fn (OrderPdfOrder $order): int => count($order->lineItems),
                $this->orders,
            )),
        ];
    }
}
