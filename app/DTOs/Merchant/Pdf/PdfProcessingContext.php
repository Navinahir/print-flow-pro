<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

use App\Enums\PdfProcessingMode;
use App\Enums\PdfProcessingStatus;

/**
 * Immutable state passed through the PDF processing pipeline.
 */
final class PdfProcessingContext
{
    /**
     * @param  list<string>  $sourceRelativePaths  Paths relative to temp disk
     * @param  list<PdfBoundaryBox>  $detectedBoundaries
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly int $uploadJobId,
        public readonly int $merchantId,
        public readonly string $countryCode,
        public readonly PdfProcessingMode $mode,
        public PdfProcessingStatus $status = PdfProcessingStatus::Pending,
        public array $sourceRelativePaths = [],
        public ?PdfCanvasSpec $canvas = null,
        public array $detectedBoundaries = [],
        public ?PdfTempPath $workDirectory = null,
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function withStatus(PdfProcessingStatus $status, array $metadata = []): self
    {
        return new self(
            uploadJobId: $this->uploadJobId,
            merchantId: $this->merchantId,
            countryCode: $this->countryCode,
            mode: $this->mode,
            status: $status,
            sourceRelativePaths: $this->sourceRelativePaths,
            canvas: $this->canvas,
            detectedBoundaries: $this->detectedBoundaries,
            workDirectory: $this->workDirectory,
            metadata: array_merge($this->metadata, $metadata),
        );
    }

    public function withCanvas(PdfCanvasSpec $canvas): self
    {
        return new self(
            uploadJobId: $this->uploadJobId,
            merchantId: $this->merchantId,
            countryCode: $this->countryCode,
            mode: $this->mode,
            status: $this->status,
            sourceRelativePaths: $this->sourceRelativePaths,
            canvas: $canvas,
            detectedBoundaries: $this->detectedBoundaries,
            workDirectory: $this->workDirectory,
            metadata: $this->metadata,
        );
    }

    /**
     * @param  list<PdfBoundaryBox>  $boundaries
     */
    public function withBoundaries(array $boundaries): self
    {
        return new self(
            uploadJobId: $this->uploadJobId,
            merchantId: $this->merchantId,
            countryCode: $this->countryCode,
            mode: $this->mode,
            status: $this->status,
            sourceRelativePaths: $this->sourceRelativePaths,
            canvas: $this->canvas,
            detectedBoundaries: $boundaries,
            workDirectory: $this->workDirectory,
            metadata: $this->metadata,
        );
    }

    public function withWorkDirectory(PdfTempPath $workDirectory): self
    {
        return new self(
            uploadJobId: $this->uploadJobId,
            merchantId: $this->merchantId,
            countryCode: $this->countryCode,
            mode: $this->mode,
            status: $this->status,
            sourceRelativePaths: $this->sourceRelativePaths,
            canvas: $this->canvas,
            detectedBoundaries: $this->detectedBoundaries,
            workDirectory: $workDirectory,
            metadata: $this->metadata,
        );
    }
}
