<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingMode;

/**
 * Module-specific processors (thermal, merge, delivery, picking).
 */
interface PdfProcessorInterface
{
    public function supports(PdfProcessingMode $mode): bool;

    public function normalize(PdfProcessingContext $context): PdfNormalizationResult;
}
