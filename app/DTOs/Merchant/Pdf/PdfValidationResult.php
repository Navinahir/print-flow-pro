<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

use App\Enums\PdfValidationCode;

final readonly class PdfValidationResult
{
    /**
     * @param  list<PdfValidationCode>  $codes
     * @param  list<string>  $messages
     */
    public function __construct(
        public bool $passed,
        public array $codes = [],
        public array $messages = [],
    ) {}

    public static function valid(): self
    {
        return new self(true, [PdfValidationCode::Valid], [PdfValidationCode::Valid->message()]);
    }

    public static function failed(PdfValidationCode $code, ?string $message = null): self
    {
        return new self(
            passed: false,
            codes: [$code],
            messages: [$message ?? $code->message()],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'codes' => array_map(static fn (PdfValidationCode $code): string => $code->value, $this->codes),
            'messages' => $this->messages,
        ];
    }
}
