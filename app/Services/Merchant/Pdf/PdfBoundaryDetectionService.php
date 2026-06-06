<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfBoundaryDetectionInterface;
use App\DTOs\Merchant\Pdf\PdfBoundaryBox;
use App\Exceptions\Merchant\Pdf\PdfBoundaryDetectionException;
use App\Services\Merchant\Pdf\Support\FpdiDocumentAdapter;
use Throwable;

/**
 * Reads PDF page geometry via FPDI — foundation for thermal/A4 validation in later phases.
 */
class PdfBoundaryDetectionService implements PdfBoundaryDetectionInterface
{
    public function __construct(
        private readonly FpdiDocumentAdapter $fpdiAdapter,
    ) {}

    public function detectFromFile(string $absolutePath, int $pageNumber = 1): PdfBoundaryBox
    {
        try {
            $this->fpdiAdapter->open($absolutePath);
            $pageCount = $this->fpdiAdapter->pageCount();

            if ($pageNumber < 1 || $pageNumber > $pageCount) {
                throw PdfBoundaryDetectionException::invalidPage($pageNumber, $pageCount);
            }

            $size = $this->fpdiAdapter->pageSizeMm($pageNumber);

            return new PdfBoundaryBox(
                pageNumber: $pageNumber,
                widthMm: $size['width_mm'],
                heightMm: $size['height_mm'],
            );
        } catch (PdfBoundaryDetectionException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw PdfBoundaryDetectionException::unreadable($absolutePath);
        } finally {
            $this->fpdiAdapter->close();
        }
    }

    public function pageCount(string $absolutePath): int
    {
        try {
            $this->fpdiAdapter->open($absolutePath);
            $pageCount = $this->fpdiAdapter->pageCount();

            return $pageCount;
        } catch (PdfBoundaryDetectionException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw PdfBoundaryDetectionException::unreadable($absolutePath);
        } finally {
            $this->fpdiAdapter->close();
        }
    }
}
