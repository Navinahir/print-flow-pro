<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Pipeline\Stages;

use App\Contracts\Merchant\Pdf\PdfPipelineStageInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingStatus;

class FinalizeProcessingStage implements PdfPipelineStageInterface
{
    public function handle(PdfProcessingContext $context): PdfProcessingContext
    {
        if ($context->status === PdfProcessingStatus::Failed) {
            return $context;
        }

        return $context->withStatus($context->status, [
            'finalized_at' => now()->toIso8601String(),
        ]);
    }

    public function name(): string
    {
        return 'finalize';
    }
}
