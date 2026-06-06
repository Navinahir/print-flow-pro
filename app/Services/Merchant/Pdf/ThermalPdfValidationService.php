<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfConfigurationInterface;
use App\Contracts\Merchant\Pdf\ThermalPdfValidationInterface;
use App\DTOs\Merchant\Pdf\PdfBoundaryBox;
use App\DTOs\Merchant\Pdf\PdfEngineConfiguration;
use App\DTOs\Merchant\Pdf\PdfValidationResult;
use App\DTOs\Merchant\Pdf\ThermalPageMetrics;
use App\Enums\PdfValidationCode;

/**
 * Validates courier thermal labels (10×15 cm family) and rejects A4 / unsupported sizes.
 */
class ThermalPdfValidationService implements ThermalPdfValidationInterface
{
    public function __construct(
        private readonly PdfConfigurationInterface $configurationService,
    ) {}

    public function analyzeBoundary(PdfBoundaryBox $boundary): ThermalPageMetrics
    {
        return ThermalPageMetrics::fromBoundary($boundary);
    }

    public function validateMetrics(ThermalPageMetrics $metrics): PdfValidationResult
    {
        $configuration = $this->configurationService->configuration();

        if ($this->matchesA4($metrics, $configuration)) {
            return PdfValidationResult::failed(PdfValidationCode::A4Rejected);
        }

        if (! $this->isSupportedThermalSize($metrics, $configuration)) {
            return PdfValidationResult::failed(
                PdfValidationCode::ThermalSizeRejected,
                __('merchant.pdf.validation.thermal_size_rejected_detail', [
                    'width' => $metrics->widthMm,
                    'height' => $metrics->heightMm,
                    'page' => $metrics->pageNumber,
                ]),
            );
        }

        return PdfValidationResult::valid();
    }

    public function validateBoundary(PdfBoundaryBox $boundary): PdfValidationResult
    {
        return $this->validateMetrics($this->analyzeBoundary($boundary));
    }

    private function matchesA4(ThermalPageMetrics $metrics, PdfEngineConfiguration $configuration): bool
    {
        $widthDelta = abs($metrics->widthMm - $configuration->a4WidthMm);
        $heightDelta = abs($metrics->heightMm - $configuration->a4HeightMm);
        $swappedWidthDelta = abs($metrics->widthMm - $configuration->a4HeightMm);
        $swappedHeightDelta = abs($metrics->heightMm - $configuration->a4WidthMm);

        $portraitMatch = $widthDelta <= $configuration->a4ToleranceMm
            && $heightDelta <= $configuration->a4ToleranceMm;

        $landscapeMatch = $swappedWidthDelta <= $configuration->a4ToleranceMm
            && $swappedHeightDelta <= $configuration->a4ToleranceMm;

        return $portraitMatch || $landscapeMatch;
    }

    private function isSupportedThermalSize(ThermalPageMetrics $metrics, PdfEngineConfiguration $configuration): bool
    {
        $validation = config('pdf.validation', []);

        $minShort = (float) ($validation['thermal_min_width_mm'] ?? 90.0);
        $maxShort = (float) ($validation['thermal_max_width_mm'] ?? 110.0);
        $minLong = (float) ($validation['thermal_min_height_mm'] ?? 140.0);
        $maxLong = (float) ($validation['thermal_max_height_mm'] ?? 160.0);

        return $metrics->shortSideMm >= $minShort
            && $metrics->shortSideMm <= $maxShort
            && $metrics->longSideMm >= $minLong
            && $metrics->longSideMm <= $maxLong;
    }
}
