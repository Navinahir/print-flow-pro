<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

final readonly class PdfCanvasSpec
{
    public function __construct(
        public float $widthMm,
        public float $heightMm,
        public float $safeZoneInsetMm,
        public float $aspectRatio,
    ) {}

    public function safeArea(): PdfPageDimensions
    {
        $inset = max(0.0, $this->safeZoneInsetMm) * 2;

        return new PdfPageDimensions(
            widthMm: max(0.0, $this->widthMm - $inset),
            heightMm: max(0.0, $this->heightMm - $inset),
        );
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'width_mm' => $this->widthMm,
            'height_mm' => $this->heightMm,
            'safe_zone_inset_mm' => $this->safeZoneInsetMm,
            'aspect_ratio' => $this->aspectRatio,
            'safe_area' => $this->safeArea()->toArray(),
        ];
    }
}
