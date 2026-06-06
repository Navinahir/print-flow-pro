<?php

declare(strict_types=1);

namespace App\Exceptions\Merchant\Pdf;

class PdfBoundaryDetectionException extends PdfEngineException
{
    public static function unreadable(string $path): self
    {
        return new self(__('merchant.pdf.boundary.unreadable', [
            'path' => basename($path),
        ]));
    }

    public static function invalidPage(int $pageNumber, int $pageCount): self
    {
        return new self(__('merchant.pdf.boundary.invalid_page', [
            'page' => $pageNumber,
            'total' => $pageCount,
        ]));
    }
}
