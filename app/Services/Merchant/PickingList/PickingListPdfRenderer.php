<?php

declare(strict_types=1);

namespace App\Services\Merchant\PickingList;

use App\DTOs\Merchant\PickingList\PickingListDocument;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class PickingListPdfRenderer
{
    /**
     * @throws PdfNormalizationException
     */
    public function renderToFile(PickingListDocument $document, string $outputAbsolutePath): void
    {
        $directory = dirname($outputAbsolutePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw PdfNormalizationException::failed('Could not create output directory.');
        }

        $config = config('pdf.picking_list', []);
        $columns = is_array($config['columns'] ?? null) ? $config['columns'] : [];

        $html = View::make('pdf.picking-list', [
            'title' => (string) ($config['title'] ?? __('merchant.picking_list.pdf.title')),
            'accountLabel' => (string) ($config['account_label'] ?? __('merchant.picking_list.pdf.account_label')),
            'generatedAtLabel' => (string) ($config['generated_at_label'] ?? __('merchant.picking_list.pdf.generated_at_label')),
            'packageLabel' => (string) ($config['package_label'] ?? __('merchant.picking_list.pdf.package_label')),
            'accountName' => $document->accountName,
            'generatedAt' => $document->generatedAt,
            'rows' => $document->rows,
            'columns' => [
                'main_sku' => (string) ($columns['main_sku'] ?? __('merchant.picking_list.pdf.columns.main_sku')),
                'image' => (string) ($columns['image'] ?? __('merchant.picking_list.pdf.columns.image')),
                'product_name' => (string) ($columns['product_name'] ?? __('merchant.picking_list.pdf.columns.product_name')),
                'variant_sku' => (string) ($columns['variant_sku'] ?? __('merchant.picking_list.pdf.columns.variant_sku')),
                'variant_name' => (string) ($columns['variant_name'] ?? __('merchant.picking_list.pdf.columns.variant_name')),
                'quantity' => (string) ($columns['quantity'] ?? __('merchant.picking_list.pdf.columns.quantity')),
                'order_sn' => (string) ($columns['order_sn'] ?? __('merchant.picking_list.pdf.columns.order_sn')),
            ],
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'autoScriptToLang' => ! app()->environment('testing'),
            'autoLangToFont' => ! app()->environment('testing'),
            'tempDir' => storage_path('app/temp/mpdf'),
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output($outputAbsolutePath, 'F');
    }
}
