<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\ParsedShopeeOrder;
use App\DTOs\Merchant\Pdf\ShopeeOrderPdfParseResult;
use Smalot\PdfParser\Parser;
use Throwable;

/**
 * Extracts Shopee Taiwan order-detail rows from exported order PDF text.
 */
class ShopeeOrderPdfParser
{
    public function __construct(
        private readonly Parser $parser,
    ) {}

    public function parseFile(string $absolutePath): ShopeeOrderPdfParseResult
    {
        if (! is_readable($absolutePath)) {
            return ShopeeOrderPdfParseResult::empty();
        }

        try {
            $text = $this->parser->parseFile($absolutePath)->getText();
        } catch (Throwable) {
            return ShopeeOrderPdfParseResult::empty();
        }

        return $this->parseText($text);
    }

    public function parseText(string $text): ShopeeOrderPdfParseResult
    {
        $normalized = $this->normalizeText($text);
        $sections = preg_split('/(?=商品列表)/u', $normalized) ?: [];
        $orders = [];

        foreach ($sections as $section) {
            $section = trim($section);

            if ($section === '' || ! str_contains($section, '訂單編號')) {
                continue;
            }

            $orderNumber = $this->extractOrderNumber($section);
            $buyerNote = $this->extractBuyerNote($section);
            $lineItems = $this->extractLineItems($section);

            if ($orderNumber === null && $lineItems === []) {
                continue;
            }

            $orders[] = new ParsedShopeeOrder(
                orderNumber: $orderNumber,
                lineItems: $lineItems,
                buyerNote: $buyerNote,
            );
        }

        $flatLineItems = [];

        foreach ($orders as $order) {
            foreach ($order->lineItems as $lineItem) {
                $flatLineItems[] = $lineItem;
            }
        }

        return new ShopeeOrderPdfParseResult($orders, $flatLineItems);
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
    }

    private function extractOrderNumber(string $section): ?string
    {
        if (preg_match('/訂單編號\s*\(單\)\s*:\s*(.+?)(?:\n|$)/u', $section, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    private function extractBuyerNote(string $section): ?string
    {
        if (preg_match('/買家備註\s*:\s*\n(.*?)\n#\s*主商品貨號/us', $section, $matches) !== 1) {
            return null;
        }

        $note = trim(preg_replace('/\s+/u', ' ', $matches[1]) ?? $matches[1]);

        return $note !== '' ? $note : null;
    }

    /**
     * @return list<array{sku: string, name: string, qty: int, price: string}>
     */
    private function extractLineItems(string $section): array
    {
        if (! preg_match('/#\s*主商品貨號.*?\n(.*)$/us', $section, $matches)) {
            return [];
        }

        $tableBody = (string) $matches[1];
        $lines = array_values(array_filter(
            explode("\n", $tableBody),
            static fn (string $line): bool => trim($line) !== '',
        ));

        $items = [];
        $buffer = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\t(.*)$/u', $line, $rowMatch) === 1) {
                if ($buffer !== []) {
                    $item = $this->finalizeBufferedItem($buffer);

                    if ($item !== null) {
                        $items[] = $item;
                    }
                }

                $buffer = [trim($rowMatch[2])];

                continue;
            }

            if ($buffer !== []) {
                $buffer[] = trim($line);
            }
        }

        if ($buffer !== []) {
            $item = $this->finalizeBufferedItem($buffer);

            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param  list<string>  $lines
     * @return array{sku: string, name: string, qty: int, price: string}|null
     */
    private function finalizeBufferedItem(array $lines): ?array
    {
        if ($lines === []) {
            return null;
        }

        $lastLine = array_pop($lines);

        if ($lastLine === null || $lastLine === '') {
            return null;
        }

        $sku = '';
        $nameLines = $lines;
        $qty = 1;
        $price = '';

        if (preg_match('/^(.+?)\s+(\d+)\s+(\d+)\s*$/u', $lastLine, $matches) === 1) {
            $nameLines[] = trim($matches[1]);
            $qty = (int) $matches[2];
            $price = $this->formatPrice($matches[3]);
        } elseif (preg_match('/^(\d+)\s+(\d+)\s*$/u', $lastLine, $matches) === 1) {
            $qty = (int) $matches[1];
            $price = $this->formatPrice($matches[2]);
        } else {
            $nameLines[] = $lastLine;
        }

        $name = trim(preg_replace('/\s+/u', ' ', implode(' ', $nameLines)) ?? '');

        if ($name === '') {
            return null;
        }

        return [
            'sku' => $sku,
            'name' => $name,
            'qty' => $qty,
            'price' => $price,
        ];
    }

    private function formatPrice(string $amount): string
    {
        $numeric = (int) preg_replace('/[^\d]/', '', $amount);

        return $numeric > 0 ? 'NT$ '.number_format($numeric) : '';
    }
}
