<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfNormalizationInterface;
use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use App\Exceptions\Merchant\Pdf\PdfValidationException;

/**
 * Delegates normalization to the module processor registry (logistics labels first).
 */
class PdfNormalizationService implements PdfNormalizationInterface
{
    public function __construct(
        private readonly PdfProcessorRegistry $processorRegistry,
    ) {}

    public function normalize(PdfProcessingContext $context): PdfNormalizationResult
    {
        $processor = $this->processorRegistry->resolve($context->mode);

        if ($processor === null) {
            return PdfNormalizationResult::deferred(
                __('merchant.pdf.normalization.deferred_for_mode', [
                    'mode' => $context->mode->label(),
                ]),
            );
        }

        try {
            return $processor->normalize($context);
        } catch (PdfValidationException|PdfNormalizationException $exception) {
            throw $exception;
        } catch (\Throwable $throwable) {
            throw PdfNormalizationException::failed($throwable->getMessage());
        }
    }
}
