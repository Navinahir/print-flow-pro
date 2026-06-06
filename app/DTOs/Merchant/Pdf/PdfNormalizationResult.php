<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

final readonly class PdfNormalizationResult
{
    /**
     * @param  list<string>  $outputRelativePaths
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public bool $implemented,
        public bool $success,
        public array $outputRelativePaths = [],
        public array $metadata = [],
        public ?string $message = null,
    ) {}

    public static function deferred(?string $message = null): self
    {
        return new self(
            implemented: false,
            success: true,
            message: $message ?? __('merchant.pdf.normalization.deferred'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'implemented' => $this->implemented,
            'success' => $this->success,
            'output_relative_paths' => $this->outputRelativePaths,
            'metadata' => $this->metadata,
            'message' => $this->message,
        ];
    }
}
