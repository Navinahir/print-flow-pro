<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;

/**
 * Reusable thermal-label normalization onto the 150×100 mm label slot canvas.
 */
interface ThermalPdfNormalizationInterface
{
    /**
     * Normalize one source page onto a 150×100 mm label slot PDF.
     */
    public function renderLabelSlot(
        string $sourceAbsolutePath,
        int $pageNumber,
        PdfProcessingContext $context,
        string $outputAbsolutePath,
    ): PdfNormalizationResult;

    /**
     * @deprecated Use renderLabelSlot() — kept for backward compatibility in tests.
     */
    public function normalizePage(
        string $sourceAbsolutePath,
        int $pageNumber,
        PdfProcessingContext $context,
        string $outputAbsolutePath,
    ): PdfNormalizationResult;
}
