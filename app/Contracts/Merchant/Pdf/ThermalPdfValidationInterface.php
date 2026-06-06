<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfBoundaryBox;
use App\DTOs\Merchant\Pdf\PdfValidationResult;
use App\DTOs\Merchant\Pdf\ThermalPageMetrics;

/**
 * Reusable thermal-label validation (10×15 cm family, A4 rejection).
 */
interface ThermalPdfValidationInterface
{
    public function analyzeBoundary(PdfBoundaryBox $boundary): ThermalPageMetrics;

    public function validateMetrics(ThermalPageMetrics $metrics): PdfValidationResult;

    public function validateBoundary(PdfBoundaryBox $boundary): PdfValidationResult;
}
