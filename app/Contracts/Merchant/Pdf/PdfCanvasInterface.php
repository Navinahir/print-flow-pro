<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfCanvasSpec;
use App\DTOs\Merchant\Pdf\PdfEngineConfiguration;
use App\DTOs\Merchant\Pdf\PdfPageDimensions;

/**
 * Builds canvas geometry shared by preview UI and PDF output pipelines.
 */
interface PdfCanvasInterface
{
    public function buildCanvasSpec(?PdfEngineConfiguration $configuration = null): PdfCanvasSpec;

    public function safeAreaDimensions(PdfCanvasSpec $canvas): PdfPageDimensions;

    public function drawableAreaDimensions(PdfCanvasSpec $canvas): PdfPageDimensions;
}
