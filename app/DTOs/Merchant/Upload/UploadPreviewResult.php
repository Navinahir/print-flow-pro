<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Upload;

final readonly class UploadPreviewResult
{
    /**
     * @param  array<string, mixed>|null  $preview
     */
    public function __construct(
        public bool $available,
        public ?array $preview,
        public ?string $previewType,
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
        ];
    }
}
