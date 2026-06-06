<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Pipeline\Stages;

use App\Contracts\Merchant\Pdf\PdfCanvasInterface;
use App\Contracts\Merchant\Pdf\PdfConfigurationInterface;
use App\Contracts\Merchant\Pdf\PdfPipelineStageInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingStatus;

class PrepareCanvasStage implements PdfPipelineStageInterface
{
    public function __construct(
        private readonly PdfCanvasInterface $canvasService,
        private readonly PdfConfigurationInterface $configurationService,
    ) {}

    public function handle(PdfProcessingContext $context): PdfProcessingContext
    {
        $configuration = $this->configurationService->configuration();
        $canvas = $this->canvasService->buildCanvasSpec($configuration);

        return $context
            ->withCanvas($canvas)
            ->withStatus(PdfProcessingStatus::CanvasPrepared, [
                'canvas' => $canvas->toArray(),
            ]);
    }

    public function name(): string
    {
        return 'prepare_canvas';
    }
}
