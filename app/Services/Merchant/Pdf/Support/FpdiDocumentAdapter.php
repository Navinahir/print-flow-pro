<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Support;

use setasign\Fpdi\Fpdi;

/**
 * Thin wrapper around setasign/fpdi for page inspection and future merge operations.
 * Normalization logic is intentionally not implemented in the foundation phase.
 */
class FpdiDocumentAdapter
{
    private ?Fpdi $document = null;

    private ?string $sourcePath = null;

    private int $totalPages = 0;

    /**
     * Opens a PDF from an absolute filesystem path.
     */
    public function open(string $absolutePath): self
    {
        $this->close();

        if (! is_readable($absolutePath)) {
            throw new \InvalidArgumentException("PDF source is not readable: {$absolutePath}");
        }

        $document = new Fpdi;
        $pageCount = $document->setSourceFile($absolutePath);

        if ($pageCount < 1) {
            throw new \RuntimeException('PDF source contains no pages.');
        }

        $this->document = $document;
        $this->sourcePath = $absolutePath;
        $this->totalPages = $pageCount;

        return $this;
    }

    public function pageCount(): int
    {
        return $this->totalPages;
    }

    /**
     * Returns page width/height in millimeters for the given 1-based page index.
     *
     * @return array{width_mm: float, height_mm: float}
     */
    public function pageSizeMm(int $pageNumber): array
    {
        $document = $this->requireDocument();

        if ($pageNumber < 1 || $pageNumber > $this->totalPages) {
            throw new \OutOfRangeException("Page {$pageNumber} is out of range.");
        }

        $document->setSourceFile((string) $this->sourcePath);
        $templateId = $document->importPage($pageNumber);
        $size = $document->getTemplateSize($templateId);

        $widthPt = (float) ($size['width'] ?? 0);
        $heightPt = (float) ($size['height'] ?? 0);

        return [
            'width_mm' => $this->normalizeDimensionToMm($widthPt),
            'height_mm' => $this->normalizeDimensionToMm($heightPt),
        ];
    }

    /**
     * Exposes the underlying FPDI instance for future merge/normalization phases.
     */
    public function instance(): Fpdi
    {
        return $this->requireDocument();
    }

    public function sourcePath(): ?string
    {
        return $this->sourcePath;
    }

    public function close(): void
    {
        $this->document = null;
        $this->sourcePath = null;
        $this->totalPages = 0;
    }

    private function requireDocument(): Fpdi
    {
        if ($this->document === null) {
            throw new \RuntimeException('No PDF document is open. Call open() first.');
        }

        return $this->document;
    }

    /**
     * Template sizes follow the FPDI document unit (mm by default).
     */
    private function normalizeDimensionToMm(float $value): float
    {
        if ($value <= 0) {
            return 0.0;
        }

        $unit = (string) config('pdf.fpdi.unit', 'mm');

        if ($unit === 'mm') {
            return round($value, 2);
        }

        return $this->pointsToMm($value);
    }

    private function pointsToMm(float $points): float
    {
        return round($points * 25.4 / 72, 2);
    }
}
