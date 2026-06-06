<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfCanvasInterface;
use App\Contracts\Merchant\Pdf\PdfConfigurationInterface;
use App\DTOs\Merchant\Pdf\PdfCanvasSpec;
use App\DTOs\Merchant\Pdf\PdfEngineConfiguration;
use App\DTOs\Merchant\Pdf\PdfPageDimensions;

/**
 * Shared canvas geometry for thermal normalization and M1 preview alignment.
 */
class PdfCanvasService implements PdfCanvasInterface
{
    public function __construct(
        private readonly PdfConfigurationInterface $configurationService,
    ) {}

    public function buildCanvasSpec(?PdfEngineConfiguration $configuration = null): PdfCanvasSpec
    {
        return ($configuration ?? $this->configurationService->configuration())->canvas;
    }

    public function safeAreaDimensions(PdfCanvasSpec $canvas): PdfPageDimensions
    {
        return $canvas->safeArea();
    }

    public function drawableAreaDimensions(PdfCanvasSpec $canvas): PdfPageDimensions
    {
        // Alias for safe area — normalization modules place content inside the inset guide.
        return $this->safeAreaDimensions($canvas);
    }
}
