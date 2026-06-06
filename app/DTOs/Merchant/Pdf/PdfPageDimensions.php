<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

final readonly class PdfPageDimensions
{
    public function __construct(
        public float $widthMm,
        public float $heightMm,
    ) {}

    public function aspectRatio(): float
    {
        if ($this->heightMm <= 0) {
            return 0.0;
        }

        return $this->widthMm / $this->heightMm;
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'width_mm' => $this->widthMm,
            'height_mm' => $this->heightMm,
            'aspect_ratio' => $this->aspectRatio(),
        ];
    }
}
