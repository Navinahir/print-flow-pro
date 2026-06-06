<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfProcessingContext;

/**
 * Single step in the PDF processing pipeline.
 */
interface PdfPipelineStageInterface
{
    public function handle(PdfProcessingContext $context): PdfProcessingContext;

    public function name(): string;
}
