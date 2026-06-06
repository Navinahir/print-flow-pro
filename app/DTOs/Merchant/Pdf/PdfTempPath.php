<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Pdf;

final readonly class PdfTempPath
{
    public function __construct(
        public string $disk,
        public string $relativePath,
    ) {}

    public function withSuffix(string $suffix): self
    {
        return new self($this->disk, rtrim($this->relativePath, '/').'/'.ltrim($suffix, '/'));
    }
}
