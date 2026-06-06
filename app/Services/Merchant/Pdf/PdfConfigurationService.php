<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfConfigurationInterface;
use App\DTOs\Merchant\Pdf\PdfCanvasSpec;
use App\DTOs\Merchant\Pdf\PdfEngineConfiguration;
use App\Services\Merchant\Preview\PreviewConfigurationService;

/**
 * Merges config/pdf.php defaults with domain-driven preview settings from MerchantConfig.
 */
class PdfConfigurationService implements PdfConfigurationInterface
{
    public function __construct(
        private readonly PreviewConfigurationService $previewConfigurationService,
    ) {}

    public function configuration(): PdfEngineConfiguration
    {
        $preview = $this->previewConfigurationService->configuration();

        $canvas = new PdfCanvasSpec(
            widthMm: $preview->widthMm,
            heightMm: $preview->heightMm,
            safeZoneInsetMm: $preview->safeZoneInsetMm,
            aspectRatio: $preview->aspectRatio,
        );

        $validation = config('pdf.validation', []);

        return new PdfEngineConfiguration(
            tempDisk: (string) config('pdf.temp_disk', 'temp'),
            outputTtlMinutes: (int) config('pdf.output_ttl_minutes', 10),
            downloadGraceSeconds: (int) config('pdf.download_grace_seconds', 30),
            shredOnDownload: (bool) config('pdf.shred_on_download', true),
            maxSourceBytes: (int) config('pdf.max_source_bytes', 52_428_800),
            maxPagesPerJob: (int) config('pdf.max_pages_per_job', 500),
            canvas: $canvas,
            aspectTolerancePercent: (float) ($validation['aspect_tolerance_percent'] ?? 10.0),
            a4WidthMm: (float) ($validation['a4_width_mm'] ?? 210.0),
            a4HeightMm: (float) ($validation['a4_height_mm'] ?? 297.0),
            a4ToleranceMm: (float) ($validation['a4_tolerance_mm'] ?? 3.0),
        );
    }
}
