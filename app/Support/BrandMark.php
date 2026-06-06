<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class BrandMark
{
    /**
     * Build a short brand monogram (e.g. "XY Cubic Shopee" → "XY").
     */
    public static function initials(string $brandName): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $brandName) ?? '');

        if ($normalized === '') {
            return '?';
        }

        $words = preg_split('/\s+/u', $normalized) ?: [];
        $firstWord = $words[0] ?? '';

        if ($firstWord !== '' && mb_strlen($firstWord) <= 3) {
            return mb_strtoupper(mb_substr($firstWord, 0, 2));
        }

        return UserAvatar::initials($brandName);
    }

    public static function logoUrl(): ?string
    {
        $logo = MerchantConfig::get('brand.logo');

        if (! is_string($logo) || $logo === '') {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($logo)) {
            return null;
        }

        return $disk->url($logo);
    }
}
