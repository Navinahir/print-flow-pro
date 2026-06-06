<?php

declare(strict_types=1);

namespace App\Enums;

enum PdfPageOrientation: string
{
    case Portrait = 'portrait';
    case Landscape = 'landscape';

    public function label(): string
    {
        return match ($this) {
            self::Portrait => __('merchant.pdf.orientation.portrait'),
            self::Landscape => __('merchant.pdf.orientation.landscape'),
        };
    }

    public static function fromDimensions(float $widthMm, float $heightMm): self
    {
        return $widthMm >= $heightMm ? self::Landscape : self::Portrait;
    }
}
