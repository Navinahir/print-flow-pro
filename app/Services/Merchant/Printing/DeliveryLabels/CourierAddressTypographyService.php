<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing\DeliveryLabels;

final class CourierAddressTypographyService
{
    public const DEFAULT_FONT_SIZE_PX = 18;

    public const MIN_FONT_SIZE_PX = 14;

    public const SHRINK_THRESHOLD_CHARS = 35;

    public const MAX_SINGLE_LINE_CHARS = 42;

    public function resolveFontSizePx(string $address): int
    {
        $length = mb_strlen(trim($address));

        if ($length <= self::SHRINK_THRESHOLD_CHARS) {
            return self::DEFAULT_FONT_SIZE_PX;
        }

        $scaled = (int) floor(self::DEFAULT_FONT_SIZE_PX * self::SHRINK_THRESHOLD_CHARS / $length);

        return max(self::MIN_FONT_SIZE_PX, min(self::DEFAULT_FONT_SIZE_PX, $scaled));
    }

    /**
     * @return list<string>
     */
    public function wrapAddressLines(string $address, ?int $maxLineLength = null): array
    {
        $trimmed = trim($address);

        if ($trimmed === '') {
            return [];
        }

        if (str_contains($trimmed, "\n") || str_contains($trimmed, "\r")) {
            return array_values(array_filter(array_map(
                static fn (string $line): string => trim($line),
                preg_split('/\R/u', $trimmed) ?: [],
            ), static fn (string $line): bool => $line !== ''));
        }

        $normalized = preg_replace('/\s+/u', ' ', $trimmed) ?? '';

        $lineLength = $maxLineLength ?? $this->lineLengthForFontSize(
            $this->resolveFontSizePx($normalized),
        );

        return $this->wrapByWords($normalized, $lineLength);
    }

    public function lineLengthForFontSize(int $fontSizePx): int
    {
        $ratio = $fontSizePx / self::DEFAULT_FONT_SIZE_PX;

        return max(24, (int) floor(self::MAX_SINGLE_LINE_CHARS / max($ratio, 0.75)));
    }

    /**
     * @return list<string>
     */
    private function wrapByWords(string $text, int $maxLineLength): array
    {
        if (mb_strlen($text) <= $maxLineLength) {
            return [$text];
        }

        $words = preg_split('/\s+/u', $text) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if (mb_strlen($candidate) <= $maxLineLength) {
                $current = $candidate;

                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            if (mb_strlen($word) > $maxLineLength) {
                $lines = array_merge($lines, $this->chunkLongWord($word, $maxLineLength));
                $current = '';

                continue;
            }

            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function chunkLongWord(string $word, int $maxLineLength): array
    {
        $chunks = [];

        for ($offset = 0; $offset < mb_strlen($word); $offset += $maxLineLength) {
            $chunks[] = mb_substr($word, $offset, $maxLineLength);
        }

        return $chunks;
    }
}
