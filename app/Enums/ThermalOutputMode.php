<?php

declare(strict_types=1);

namespace App\Enums;

enum ThermalOutputMode: string
{
    case Combined = 'combined';
    case Separate = 'separate';

    public function label(): string
    {
        return match ($this) {
            self::Combined => (string) __('merchant.uploads.form.thermal_output_combined'),
            self::Separate => (string) __('merchant.uploads.form.thermal_output_separate'),
        };
    }

    public static function fromUploadMetadata(?array $metadata, int $fileCount): self
    {
        if ($fileCount <= 1) {
            return self::Combined;
        }

        $value = is_array($metadata) ? ($metadata['thermal_output_mode'] ?? null) : null;

        return self::tryFrom((string) $value) ?? self::Combined;
    }
}
