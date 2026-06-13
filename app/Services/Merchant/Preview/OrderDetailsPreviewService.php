<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\DTOs\Merchant\Pdf\ParsedShopeeOrder;
use App\DTOs\Merchant\Pdf\PdfTempPath;
use App\DTOs\Merchant\Pdf\ShopeeOrderPdfParseResult;
use App\DTOs\Merchant\Preview\OrderDetailsPreviewData;
use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use App\Services\Merchant\Pdf\PdfTempStorageService;
use App\Services\Merchant\Pdf\ShopeeOrderPdfParser;
use Illuminate\Support\Facades\Storage;

class OrderDetailsPreviewService
{
    public function __construct(
        private readonly ShopeeOrderPdfParser $orderPdfParser,
        private readonly PdfTempStorageService $tempStorage,
    ) {}
    public function buildSamplePreview(string $suffix = '1'): OrderDetailsPreviewData
    {
        return new OrderDetailsPreviewData(
            orderNumber: (string) __('merchant.printing.preview.order_details.samples.order_number', ['id' => $suffix]),
            customerName: (string) __('merchant.printing.preview.order_details.samples.customer_name'),
            orderDate: (string) __('merchant.printing.preview.order_details.samples.order_date'),
            status: (string) __('merchant.printing.preview.order_details.samples.status'),
            lineItems: [
                [
                    'sku' => 'SKU-1001',
                    'name' => (string) __('merchant.printing.preview.order_details.samples.item_one'),
                    'qty' => 2,
                    'price' => 'NT$ 598',
                ],
                [
                    'sku' => 'SKU-2044',
                    'name' => (string) __('merchant.printing.preview.order_details.samples.item_two'),
                    'qty' => 1,
                    'price' => 'NT$ 320',
                ],
            ],
            summary: [
                'subtotal' => 'NT$ 1,516',
                'shipping' => 'NT$ 60',
                'total' => 'NT$ 1,576',
                'currency' => 'TWD',
            ],
            notes: (string) __('merchant.printing.preview.order_details.samples.notes'),
        );
    }

    public function buildFromPrintJob(PrintJob $printJob): OrderDetailsPreviewData
    {
        $pageCount = (int) ($printJob->metadata['page_count'] ?? 0);
        $canDownload = in_array($printJob->status, [PrintJobStatus::Ready, PrintJobStatus::Downloaded], true);
        $parsed = $this->resolveParsedContent($printJob);

        $downloadUrl = $canDownload
            ? route('printing.order_details.download', $printJob)
            : null;

        $previewUrl = $canDownload
            ? route('printing.order_details.preview', $printJob)
            : null;

        $orderNumbers = array_values(array_filter(array_map(
            static fn (array $order): ?string => $order['order_number'] ?? null,
            $parsed->toArray()['orders'] ?? [],
        )));

        $orderNumber = $this->formatOrderNumberLabel($orderNumbers, $printJob->id);
        $buyerNote = $this->firstBuyerNote($parsed);
        $summary = $this->buildSummary($parsed->lineItems);

        return new OrderDetailsPreviewData(
            orderNumber: $orderNumber,
            customerName: $buyerNote ?? (string) __('merchant.printing.preview.order_details.processed.customer_fallback'),
            orderDate: $printJob->created_at?->format('Y-m-d H:i') ?? '',
            status: (string) __('merchant.printing.preview.order_details.processed.status'),
            lineItems: $parsed->lineItems,
            summary: $summary,
            notes: $parsed->lineItems === []
                ? (string) __('merchant.printing.preview.order_details.processed.notes')
                : null,
            downloadUrl: $downloadUrl,
            previewUrl: $previewUrl,
            pageCount: $pageCount > 0 ? $pageCount : null,
        );
    }

    private function resolveParsedContent(PrintJob $printJob): ShopeeOrderPdfParseResult
    {
        $cached = $printJob->metadata['parsed_content'] ?? null;

        if (is_array($cached)) {
            return new ShopeeOrderPdfParseResult(
                orders: array_map(
                    static fn (array $order): ParsedShopeeOrder => new ParsedShopeeOrder(
                        orderNumber: $order['order_number'] ?? null,
                        lineItems: $order['line_items'] ?? [],
                        buyerNote: $order['buyer_note'] ?? null,
                    ),
                    $cached['orders'] ?? [],
                ),
                lineItems: $cached['line_items'] ?? [],
            );
        }

        if (! is_string($printJob->output_path) || $printJob->output_path === '') {
            return ShopeeOrderPdfParseResult::empty();
        }

        if (! Storage::disk($printJob->output_disk)->exists($printJob->output_path)) {
            return ShopeeOrderPdfParseResult::empty();
        }

        $absolutePath = $this->tempStorage->absolutePath(
            new PdfTempPath($printJob->output_disk, $printJob->output_path),
        );

        return $this->orderPdfParser->parseFile($absolutePath);
    }

    /**
     * @param  list<string>  $orderNumbers
     */
    private function formatOrderNumberLabel(array $orderNumbers, int $printJobId): string
    {
        if ($orderNumbers === []) {
            return (string) __('merchant.printing.preview.order_details.processed.order_number', [
                'job' => $printJobId,
            ]);
        }

        if (count($orderNumbers) === 1) {
            return $orderNumbers[0];
        }

        return (string) __('merchant.printing.preview.order_details.processed.multiple_orders', [
            'first' => $orderNumbers[0],
            'count' => count($orderNumbers),
        ]);
    }

    private function firstBuyerNote(ShopeeOrderPdfParseResult $parsed): ?string
    {
        foreach ($parsed->orders as $order) {
            if ($order->buyerNote !== null && $order->buyerNote !== '') {
                return $order->buyerNote;
            }
        }

        return null;
    }

    /**
     * @param  list<array{sku: string, name: string, qty: int, price: string}>  $lineItems
     * @return array{subtotal: string, shipping: string, total: string, currency: string}
     */
    private function buildSummary(array $lineItems): array
    {
        $total = 0;

        foreach ($lineItems as $lineItem) {
            $amount = (int) preg_replace('/[^\d]/', '', (string) ($lineItem['price'] ?? ''));

            if ($amount > 0) {
                $total += $amount;
            }
        }

        $formatted = $total > 0 ? 'NT$ '.number_format($total) : '';

        return [
            'subtotal' => $formatted,
            'shipping' => '',
            'total' => $formatted,
            'currency' => 'TWD',
        ];
    }
}
