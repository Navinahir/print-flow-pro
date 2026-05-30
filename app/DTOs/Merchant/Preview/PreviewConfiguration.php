<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Preview;

final readonly class PreviewConfiguration
{
    public function __construct(
        public float $widthMm,
        public float $heightMm,
        public float $aspectRatio,
        public float $safeZoneInsetMm,
        public float $defaultZoom,
        public string $scalingBehavior,
    ) {}

    public function baseWidthPx(): float
    {
        return ($this->widthMm * 96) / 25.4;
    }

    public function baseHeightPx(): float
    {
        return ($this->heightMm * 96) / 25.4;
    }

    /**
     * @return array<string, float|string>
     */
    public function toArray(): array
    {
        return [
            'width_mm' => $this->widthMm,
            'height_mm' => $this->heightMm,
            'aspect_ratio' => $this->aspectRatio,
            'safe_zone_inset_mm' => $this->safeZoneInsetMm,
            'default_zoom' => $this->defaultZoom,
            'scaling_behavior' => $this->scalingBehavior,
            'base_width_px' => $this->baseWidthPx(),
            'base_height_px' => $this->baseHeightPx(),
        ];
    }
}
