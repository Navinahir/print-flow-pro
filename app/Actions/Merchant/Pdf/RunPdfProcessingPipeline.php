<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfEngineInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfProcessingResult;
use App\Models\UploadJob;

/**
 * Runs the PDF engine framework pipeline for an upload job context.
 */
class RunPdfProcessingPipeline
{
    public function __construct(
        private readonly PreparePdfProcessingContext $prepareContext,
        private readonly PdfEngineInterface $pdfEngine,
    ) {}

    public function execute(UploadJob $uploadJob): PdfProcessingResult
    {
        $context = $this->prepareContext->execute($uploadJob);

        return $this->pdfEngine->process($context);
    }

    public function executeWithContext(PdfProcessingContext $context): PdfProcessingResult
    {
        return $this->pdfEngine->process($context);
    }
}
