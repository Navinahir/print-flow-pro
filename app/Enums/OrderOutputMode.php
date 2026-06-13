<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderOutputMode: string
{
    case Combined = 'combined';
    case Separate = 'separate';

    public function label(): string
    {
        return match ($this) {
            self::Combined => (string) __('merchant.uploads.form.order_output_combined'),
            self::Separate => (string) __('merchant.uploads.form.order_output_separate'),
        };
    }

    public static function fromUploadMetadata(?array $metadata, int $fileCount): self
    {
        if ($fileCount <= 1) {
            return self::Combined;
        }

        $value = is_array($metadata) ? ($metadata['order_output_mode'] ?? null) : null;

        return self::tryFrom((string) $value) ?? self::Combined;
    }
}
