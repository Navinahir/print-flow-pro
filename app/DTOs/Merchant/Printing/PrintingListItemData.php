<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Printing;

final readonly class PrintingListItemData
{
    public function __construct(
        public string $id,
        public string $title,
        public string $subtitle,
        public string $status = 'pending',
        public ?string $meta = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?array $preview = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'status' => $this->status,
            'meta' => $this->meta,
            'width' => $this->width,
            'height' => $this->height,
            'preview' => $this->preview,
        ];
    }
}
