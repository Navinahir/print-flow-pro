<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfValidationResult;
use App\Enums\PdfProcessingMode;

/**
 * Validates upload sources before normalization (framework rules only in foundation phase).
 */
interface PdfValidationInterface
{
    public function validateContext(PdfProcessingContext $context): PdfValidationResult;

    public function validateSourceFile(string $absolutePath, PdfProcessingMode $mode): PdfValidationResult;
}
