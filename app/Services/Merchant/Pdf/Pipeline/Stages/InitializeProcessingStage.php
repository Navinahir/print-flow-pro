<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Pipeline\Stages;

use App\Contracts\Merchant\Pdf\PdfPipelineStageInterface;
use App\Contracts\Merchant\Pdf\PdfTempStorageInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingStatus;

/**
 * Ensures work/output directories exist on the temp disk before processing continues.
 */
class InitializeProcessingStage implements PdfPipelineStageInterface
{
    public function __construct(
        private readonly PdfTempStorageInterface $tempStorage,
    ) {}

    public function handle(PdfProcessingContext $context): PdfProcessingContext
    {
        $workDirectory = $this->tempStorage->workDirectory($context->merchantId, $context->uploadJobId, 'pipeline');
        $outputsDirectory = $this->tempStorage->outputsDirectory($context->merchantId, $context->uploadJobId);

        $this->tempStorage->ensureDirectory($workDirectory);
        $this->tempStorage->ensureDirectory($outputsDirectory);

        return $context
            ->withWorkDirectory($workDirectory)
            ->withStatus(PdfProcessingStatus::Pending, [
                'outputs_directory' => $outputsDirectory->relativePath,
            ]);
    }

    public function name(): string
    {
        return 'initialize';
    }
}
