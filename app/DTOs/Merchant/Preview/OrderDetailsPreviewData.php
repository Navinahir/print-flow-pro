<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Preview;

use App\Contracts\Merchant\Preview\PrintingPreviewPayload;
use App\Enums\PrintingPreviewType;

final readonly class OrderDetailsPreviewData implements PrintingPreviewPayload
{
    /**
     * @param  list<array{sku: string, name: string, qty: int, price: string}>  $lineItems
     * @param  array{subtotal: string, shipping: string, total: string, currency: string}  $summary
     */
    public function __construct(
        public string $orderNumber,
        public string $customerName,
        public string $orderDate,
        public string $status,
        public array $lineItems,
        public array $summary,
        public ?string $notes = null,
        public ?string $downloadUrl = null,
        public ?string $previewUrl = null,
        public ?int $pageCount = null,
    ) {}

    public function type(): PrintingPreviewType
    {
        return PrintingPreviewType::OrderDetails;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type()->value,
            'order_number' => $this->orderNumber,
            'customer_name' => $this->customerName,
            'order_date' => $this->orderDate,
            'status' => $this->status,
            'line_items' => $this->lineItems,
            'summary' => $this->summary,
            'notes' => $this->notes,
            'download_url' => $this->downloadUrl,
            'preview_url' => $this->previewUrl,
            'page_count' => $this->pageCount,
        ];
    }
}
