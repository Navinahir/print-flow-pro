<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Pipeline\Stages;

use App\Contracts\Merchant\Pdf\PdfNormalizationInterface;
use App\Contracts\Merchant\Pdf\PdfPipelineStageInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingStatus;

/**
 * Runs module-specific normalization (logistics labels, etc.).
 */
class NormalizeProcessingStage implements PdfPipelineStageInterface
{
    public function __construct(
        private readonly PdfNormalizationInterface $normalizationService,
    ) {}

    public function handle(PdfProcessingContext $context): PdfProcessingContext
    {
        $normalization = $this->normalizationService->normalize($context);

        if (! $normalization->implemented) {
            return $context->withStatus(PdfProcessingStatus::NormalizationDeferred, [
                'normalization' => $normalization->toArray(),
            ]);
        }

        if (! $normalization->success) {
            return $context->withStatus(PdfProcessingStatus::Failed, [
                'normalization' => $normalization->toArray(),
            ]);
        }

        return $context->withStatus(PdfProcessingStatus::Completed, [
            'normalization' => $normalization->toArray(),
        ]);
    }

    public function name(): string
    {
        return 'normalize';
    }
}
