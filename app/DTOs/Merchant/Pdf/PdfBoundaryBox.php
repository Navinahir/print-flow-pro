<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

final readonly class PdfBoundaryBox
{
    public function __construct(
        public int $pageNumber,
        public float $widthMm,
        public float $heightMm,
        public float $originXMm = 0.0,
        public float $originYMm = 0.0,
    ) {}

    public function dimensions(): PdfPageDimensions
    {
        return new PdfPageDimensions($this->widthMm, $this->heightMm);
    }

    /**
     * @return array<string, float|int>
     */
    public function toArray(): array
    {
        return [
            'page_number' => $this->pageNumber,
            'width_mm' => $this->widthMm,
            'height_mm' => $this->heightMm,
            'origin_x_mm' => $this->originXMm,
            'origin_y_mm' => $this->originYMm,
            'aspect_ratio' => $this->dimensions()->aspectRatio(),
        ];
    }
}
