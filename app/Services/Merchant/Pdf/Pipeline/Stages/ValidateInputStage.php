<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Pipeline\Stages;

use App\Contracts\Merchant\Pdf\PdfPipelineStageInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingStatus;
use App\Exceptions\Merchant\Pdf\PdfValidationException;
use App\Services\Merchant\Pdf\PdfValidationService;

class ValidateInputStage implements PdfPipelineStageInterface
{
    public function __construct(
        private readonly PdfValidationService $validationService,
    ) {}

    public function handle(PdfProcessingContext $context): PdfProcessingContext
    {
        try {
            $this->validationService->assertValid($context);
        } catch (PdfValidationException $exception) {
            throw $exception;
        }

        return $context->withStatus(PdfProcessingStatus::Validated);
    }

    public function name(): string
    {
        return 'validate_input';
    }
}
