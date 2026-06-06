<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Pipeline\Stages;

use App\Contracts\Merchant\Pdf\PdfBoundaryDetectionInterface;
use App\Contracts\Merchant\Pdf\PdfPipelineStageInterface;
use App\Contracts\Merchant\Pdf\PdfTempStorageInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfTempPath;
use App\Enums\PdfProcessingStatus;

/**
 * Inspects PDF sources with FPDI and stores page boundaries on the context.
 */
class DetectBoundariesStage implements PdfPipelineStageInterface
{
    public function __construct(
        private readonly PdfBoundaryDetectionInterface $boundaryDetection,
        private readonly PdfTempStorageInterface $tempStorage,
    ) {}

    public function handle(PdfProcessingContext $context): PdfProcessingContext
    {
        $boundaries = [];

        foreach ($context->sourceRelativePaths as $relativePath) {
            if (! str_ends_with(strtolower($relativePath), '.pdf')) {
                continue;
            }

            $absolutePath = $this->tempStorage->absolutePath(
                new PdfTempPath(disk: (string) config('pdf.temp_disk', 'temp'), relativePath: $relativePath),
            );

            $pageCount = $this->boundaryDetection->pageCount($absolutePath);

            for ($page = 1; $page <= $pageCount; $page++) {
                $boundaries[] = $this->boundaryDetection->detectFromFile($absolutePath, $page);
            }
        }

        return $context
            ->withBoundaries($boundaries)
            ->withStatus(PdfProcessingStatus::BoundariesDetected, [
                'boundary_count' => count($boundaries),
            ]);
    }

    public function name(): string
    {
        return 'detect_boundaries';
    }
}
