<?php

declare(strict_types=1);

namespace App\Exceptions\Merchant\Pdf;

class PdfNormalizationException extends PdfEngineException
{
    public static function notImplemented(): self
    {
        return new self(__('merchant.pdf.normalization.not_implemented'));
    }

    public static function failed(?string $detail = null): self
    {
        return new self(__('merchant.pdf.normalization.failed', [
            'detail' => $detail ?? '',
        ]));
    }
}
