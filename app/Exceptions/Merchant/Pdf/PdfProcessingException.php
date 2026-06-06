<?php

declare(strict_types=1);

namespace App\Exceptions\Merchant\Pdf;

class PdfProcessingException extends PdfEngineException
{
    public static function stageFailed(string $stage, ?string $detail = null): self
    {
        return new self(__('merchant.pdf.errors.stage_failed', [
            'stage' => $stage,
            'detail' => $detail ?? '',
        ]));
    }
}
