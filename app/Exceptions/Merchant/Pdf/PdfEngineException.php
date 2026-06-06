<?php

declare(strict_types=1);

namespace App\Exceptions\Merchant\Pdf;

use Exception;

/**
 * Base exception for the Merchant PDF engine.
 * User-facing messages are localized via lang/merchant.php keys.
 */
class PdfEngineException extends Exception
{
    public static function pipelineFailed(?string $detail = null): self
    {
        return new self(__('merchant.pdf.errors.pipeline_failed', [
            'detail' => $detail ?? '',
        ]));
    }

    public static function configurationInvalid(string $detail): self
    {
        return new self(__('merchant.pdf.errors.configuration_invalid', [
            'detail' => $detail,
        ]));
    }
}
