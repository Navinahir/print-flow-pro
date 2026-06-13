<?php

declare(strict_types=1);

namespace App\Services\Merchant\PickingList;

/**
 * Parses Shopee picking-list product_info strings from spreadsheet exports.
 */
class PickingListProductInfoParser
{
    /**
     * @return array{
     *     product_name: string,
     *     main_sku: string,
     *     variant_name: string,
     *     variant_sku: string,
     *     quantity: int,
     *     unit_price: int
     * }
     */
    public function parse(string $productInfo): array
    {
        $productInfo = trim($productInfo);

        if ($productInfo === '') {
            return [
                'product_name' => '',
                'main_sku' => '',
                'variant_name' => '',
                'variant_sku' => '',
                'quantity' => 0,
                'unit_price' => 0,
            ];
        }

        $productName = $this->matchSegment($productInfo, '/商品名稱:\s*(.*?)\s*;\s*商品選項名稱:/u')
            ?? $this->matchSegment($productInfo, '/商品名稱:\s*(.*?)\s*;/u')
            ?? '';

        $mainSku = trim($this->matchSegment($productInfo, '/主商品貨號:\s*(.*?)\s*;/u') ?? '');

        $variantName = $this->matchSegment($productInfo, '/商品選項名稱:\s*(.*?)\s*;\s*價格:/u')
            ?? $this->matchSegment($productInfo, '/商品選項名稱:\s*(.*?)\s*;/u')
            ?? '';

        $variantSku = trim($this->matchSegment($productInfo, '/商品選項貨號:\s*(.*?)\s*;/u') ?? '');

        $quantity = 0;

        if (preg_match('/數量:\s*(\d+)/u', $productInfo, $matches) === 1) {
            $quantity = max(0, (int) $matches[1]);
        }

        $unitPrice = 0;

        if (preg_match('/價格:\s*\$\s*([\d,]+)/u', $productInfo, $matches) === 1) {
            $unitPrice = max(0, (int) str_replace(',', '', $matches[1]));
        }

        return [
            'product_name' => trim($productName),
            'main_sku' => $mainSku,
            'variant_name' => trim($variantName),
            'variant_sku' => $variantSku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ];
    }

    /**
     * Maps parsed Shopee product_info fields to picking-sheet columns.
     *
     * @param  array{
     *     product_name: string,
     *     variant_name: string,
     *     variant_sku: string,
     *     quantity: int
     * }  $parsed
     * @return array{variant_sku: string, variant_name: string}
     */
    public function resolveVariantColumns(array $parsed): array
    {
        $variantSku = trim($parsed['variant_sku']);
        $variantName = trim($parsed['variant_name']);

        if ($variantSku !== '') {
            return [
                'variant_sku' => $variantSku,
                'variant_name' => $variantName,
            ];
        }

        if ($variantName === '') {
            return [
                'variant_sku' => '',
                'variant_name' => '',
            ];
        }

        if ($this->looksLikeVariantSku($variantName)) {
            return [
                'variant_sku' => $variantName,
                'variant_name' => '',
            ];
        }

        return [
            'variant_sku' => '',
            'variant_name' => $variantName,
        ];
    }

    private function looksLikeVariantSku(string $value): bool
    {
        if (preg_match('/\p{Han}/u', $value) === 1) {
            return false;
        }

        return mb_strlen($value) <= 64;
    }

    private function matchSegment(string $value, string $pattern): ?string
    {
        if (preg_match($pattern, $value, $matches) !== 1) {
            return null;
        }

        return (string) ($matches[1] ?? '');
    }
}
