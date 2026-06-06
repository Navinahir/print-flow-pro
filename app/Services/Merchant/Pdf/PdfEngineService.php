<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfEngineInterface;
use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfProcessingResult;
use App\DTOs\Merchant\Pdf\PdfValidationResult;
use App\Enums\PdfProcessingStatus;
use App\Exceptions\Merchant\Pdf\PdfEngineException;
use App\Exceptions\Merchant\Pdf\PdfValidationException;
use App\Services\Merchant\Pdf\Pipeline\PdfProcessingPipeline;
use App\Services\Merchant\Pdf\Pipeline\Stages\DetectBoundariesStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\FinalizeProcessingStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\InitializeProcessingStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\NormalizeProcessingStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\PrepareCanvasStage;
use App\Services\Merchant\Pdf\Pipeline\Stages\ValidateInputStage;

/**
 * Orchestrates validation, boundary detection, canvas prep, and module normalization.
 */
class PdfEngineService implements PdfEngineInterface
{
    public function __construct(
        private readonly InitializeProcessingStage $initializeStage,
        private readonly ValidateInputStage $validateStage,
        private readonly DetectBoundariesStage $detectBoundariesStage,
        private readonly PrepareCanvasStage $prepareCanvasStage,
        private readonly NormalizeProcessingStage $normalizeStage,
        private readonly FinalizeProcessingStage $finalizeStage,
    ) {}

    public function process(PdfProcessingContext $context): PdfProcessingResult
    {
        try {
            $context = $this->buildPipeline()->run($context);

            $normalizationData = $context->metadata['normalization'] ?? null;
            $normalization = is_array($normalizationData)
                ? new PdfNormalizationResult(
                    implemented: (bool) ($normalizationData['implemented'] ?? false),
                    success: (bool) ($normalizationData['success'] ?? true),
                    outputRelativePaths: $normalizationData['output_relative_paths'] ?? [],
                    metadata: $normalizationData['metadata'] ?? [],
                    message: $normalizationData['message'] ?? null,
                )
                : PdfNormalizationResult::deferred();

            return PdfProcessingResult::fromContext(
                $context,
                PdfValidationResult::valid(),
                $normalization,
            )->withSuccess(
                $this->isSuccessfulResult($context, $normalization),
            );
        } catch (PdfValidationException $exception) {
            return PdfProcessingResult::failed(
                context: $context,
                message: $exception->getMessage(),
                errors: [$exception->getMessage()],
                validation: PdfValidationResult::failed($exception->validationCode, $exception->getMessage()),
            );
        } catch (PdfEngineException $exception) {
            return PdfProcessingResult::failed(
                context: $context,
                message: $exception->getMessage(),
                errors: [$exception->getMessage()],
            );
        }
    }

    public function buildPipeline(): PdfProcessingPipeline
    {
        return (new PdfProcessingPipeline)
            ->pipe($this->initializeStage)
            ->pipe($this->validateStage)
            ->pipe($this->detectBoundariesStage)
            ->pipe($this->prepareCanvasStage)
            ->pipe($this->normalizeStage)
            ->pipe($this->finalizeStage);
    }

    private function isSuccessfulResult(PdfProcessingContext $context, PdfNormalizationResult $normalization): bool
    {
        if ($context->status === PdfProcessingStatus::Failed) {
            return false;
        }

        if ($normalization->implemented) {
            return $normalization->success;
        }

        return true;
    }
}
