<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

final readonly class PdfEngineConfiguration
{
    public function __construct(
        public string $tempDisk,
        public int $outputTtlMinutes,
        public int $downloadGraceSeconds,
        public bool $shredOnDownload,
        public int $maxSourceBytes,
        public int $maxPagesPerJob,
        public PdfCanvasSpec $canvas,
        public float $aspectTolerancePercent,
        public float $a4WidthMm,
        public float $a4HeightMm,
        public float $a4ToleranceMm,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'temp_disk' => $this->tempDisk,
            'output_ttl_minutes' => $this->outputTtlMinutes,
            'download_grace_seconds' => $this->downloadGraceSeconds,
            'shred_on_download' => $this->shredOnDownload,
            'max_source_bytes' => $this->maxSourceBytes,
            'max_pages_per_job' => $this->maxPagesPerJob,
            'canvas' => $this->canvas->toArray(),
            'aspect_tolerance_percent' => $this->aspectTolerancePercent,
            'a4_width_mm' => $this->a4WidthMm,
            'a4_height_mm' => $this->a4HeightMm,
            'a4_tolerance_mm' => $this->a4ToleranceMm,
        ];
    }
}
