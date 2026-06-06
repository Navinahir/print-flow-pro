<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfProcessingResult;

/**
 * Primary entry point for the stateless PDF processing framework.
 */
interface PdfEngineInterface
{
    public function process(PdfProcessingContext $context): PdfProcessingResult;
}
