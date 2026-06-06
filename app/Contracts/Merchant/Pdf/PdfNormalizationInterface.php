<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;

/**
 * Normalizes source PDFs onto the target thermal canvas.
 * Implementation deferred to module-specific M2 phases.
 */
interface PdfNormalizationInterface
{
    public function normalize(PdfProcessingContext $context): PdfNormalizationResult;
}
