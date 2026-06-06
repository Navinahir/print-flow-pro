<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

use App\Enums\PdfPageOrientation;

final readonly class ThermalPageMetrics
{
    public function __construct(
        public int $pageNumber,
        public float $widthMm,
        public float $heightMm,
        public float $shortSideMm,
        public float $longSideMm,
        public PdfPageOrientation $orientation,
    ) {}

    public static function fromBoundary(PdfBoundaryBox $boundary): self
    {
        $short = min($boundary->widthMm, $boundary->heightMm);
        $long = max($boundary->widthMm, $boundary->heightMm);

        return new self(
            pageNumber: $boundary->pageNumber,
            widthMm: $boundary->widthMm,
            heightMm: $boundary->heightMm,
            shortSideMm: $short,
            longSideMm: $long,
            orientation: PdfPageOrientation::fromDimensions($boundary->widthMm, $boundary->heightMm),
        );
    }

    /**
     * @return array<string, float|int|string>
     */
    public function toArray(): array
    {
        return [
            'page_number' => $this->pageNumber,
            'width_mm' => $this->widthMm,
            'height_mm' => $this->heightMm,
            'short_side_mm' => $this->shortSideMm,
            'long_side_mm' => $this->longSideMm,
            'orientation' => $this->orientation->value,
        ];
    }
}
