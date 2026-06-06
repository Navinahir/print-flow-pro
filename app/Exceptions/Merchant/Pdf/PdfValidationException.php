<?php

declare(strict_types=1);

namespace App\Exceptions\Merchant\Pdf;

use App\Enums\PdfValidationCode;

class PdfValidationException extends PdfEngineException
{
    public function __construct(
        public readonly PdfValidationCode $validationCode,
        ?string $message = null,
    ) {
        parent::__construct($message ?? $validationCode->message());
    }

    public static function fromCode(PdfValidationCode $code): self
    {
        return new self($code);
    }
}
