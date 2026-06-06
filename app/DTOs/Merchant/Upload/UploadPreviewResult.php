<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Upload;

final readonly class UploadPreviewResult
{
    /**
     * @param  array<string, mixed>|null  $preview
     * @param  list<array<string, mixed>>  $items
     */
    public function __construct(
        public bool $available,
        public ?array $preview,
        public ?string $previewType,
        public array $items = [],
        public ?string $statusMessage = null,
        public ?string $selectedItemId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'available' => $this->available,
            'preview' => $this->preview,
            'preview_type' => $this->previewType,
            'items' => $this->items,
            'status_message' => $this->statusMessage,
            'selected_item_id' => $this->selectedItemId,
        ];
    }
}
