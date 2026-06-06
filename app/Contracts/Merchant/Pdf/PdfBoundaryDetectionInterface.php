<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfBoundaryBox;

/**
 * Reads page geometry from PDF sources via FPDI (no normalization in foundation phase).
 */
interface PdfBoundaryDetectionInterface
{
    public function detectFromFile(string $absolutePath, int $pageNumber = 1): PdfBoundaryBox;

    public function pageCount(string $absolutePath): int;
}
