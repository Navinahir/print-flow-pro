<?php



declare(strict_types=1);



namespace App\Services\Merchant\OrderPdf;



use App\DTOs\Merchant\OrderPdf\OrderPdfDocument;

use App\DTOs\Merchant\OrderPdf\OrderPdfOrder;

use App\Exceptions\Merchant\Pdf\PdfNormalizationException;

use Illuminate\Support\Facades\View;

use Mpdf\Mpdf;



class OrderPdfRenderer

{

    private const A4_WIDTH_MM = 210.0;

    private const A4_HEIGHT_MM = 297.0;



    /**

     * @throws PdfNormalizationException

     */

    public function renderToFile(OrderPdfDocument $document, string $outputAbsolutePath): int

    {

        $directory = dirname($outputAbsolutePath);



        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {

            throw PdfNormalizationException::failed('Could not create output directory.');

        }



        $config = config('pdf.order_pdf', []);

        $columns = is_array($config['columns'] ?? null) ? $config['columns'] : [];

        $margins = is_array($config['margins'] ?? null) ? $config['margins'] : [];

        $ordersPerPage = max(1, (int) ($config['orders_per_page'] ?? 2));



        $marginLeft = (float) ($margins['left'] ?? 10);

        $marginRight = (float) ($margins['right'] ?? 10);

        $marginTop = (float) ($margins['top'] ?? 10);

        $marginBottom = (float) ($margins['bottom'] ?? 14);

        $contentHeightMm = self::A4_HEIGHT_MM - $marginTop - $marginBottom;

        $slotHeightMm = $ordersPerPage > 1 ? $contentHeightMm / 2 : $contentHeightMm;

        $midpointY = $marginTop + ($contentHeightMm / 2);

        $contentWidthMm = self::A4_WIDTH_MM - $marginLeft - $marginRight;



        $viewData = [

            'sectionTitle' => (string) ($config['section_title'] ?? __('merchant.order_pdf.pdf.section_title')),

            'orderNumberLabel' => (string) ($config['order_number_label'] ?? __('merchant.order_pdf.pdf.order_number_label')),

            'packageLabel' => (string) ($config['package_label'] ?? __('merchant.order_pdf.pdf.package_label')),

            'buyerNoteLabel' => (string) ($config['buyer_note_label'] ?? __('merchant.order_pdf.pdf.buyer_note_label')),

            'fontSize' => (int) ($config['font_size'] ?? 11),

            'headingFontSize' => (int) ($config['heading_font_size'] ?? 14),

            'tableFontSize' => (int) ($config['table_font_size'] ?? 10),

            'slotHeightMm' => $slotHeightMm,

            'slotPaddingMm' => (float) ($config['slot_padding_mm'] ?? 3),

            'columns' => [

                'main_sku' => (string) ($columns['main_sku'] ?? __('merchant.order_pdf.pdf.columns.main_sku')),

                'product_name' => (string) ($columns['product_name'] ?? __('merchant.order_pdf.pdf.columns.product_name')),

                'variant_sku' => (string) ($columns['variant_sku'] ?? __('merchant.order_pdf.pdf.columns.variant_sku')),

                'variant_name' => (string) ($columns['variant_name'] ?? __('merchant.order_pdf.pdf.columns.variant_name')),

                'quantity' => (string) ($columns['quantity'] ?? __('merchant.order_pdf.pdf.columns.quantity')),

                'total' => (string) ($columns['total'] ?? __('merchant.order_pdf.pdf.columns.total')),

            ],

        ];



        $stylesHtml = View::make('pdf.order-detail-styles', $viewData)->render();



        /** @var list<list<OrderPdfOrder>> $orderPages */

        $orderPages = array_chunk($document->orders, $ordersPerPage);

        $useCjkFonts = ! app()->environment('testing');

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font_size' => $viewData['fontSize'],
            'margin_left' => (int) $marginLeft,
            'margin_right' => (int) $marginRight,
            'margin_top' => (int) $marginTop,
            'margin_bottom' => (int) $marginBottom,
            'autoScriptToLang' => $useCjkFonts,
            'autoLangToFont' => $useCjkFonts,
            'tempDir' => storage_path('app/temp/mpdf'),
        ]);



        $mpdf->SetDisplayMode('fullpage');



        $footerTemplate = (string) ($config['page_footer'] ?? '-- {PAGENO} of {nbpg} --');

        $mpdf->SetHTMLFooter('<div style="text-align:center;font-size:9pt;color:#444;">'.$footerTemplate.'</div>');



        $mpdf->WriteHTML($stylesHtml);



        foreach ($orderPages as $pageIndex => $pageOrders) {

            if ($pageIndex > 0) {

                $mpdf->AddPage();

            }



            $topOrder = $pageOrders[0] ?? null;

            $bottomOrder = $pageOrders[1] ?? null;



            if ($topOrder !== null) {

                $this->writeOrderSlot(

                    mpdf: $mpdf,

                    viewData: $viewData,

                    order: $topOrder,

                    x: $marginLeft,

                    y: $marginTop,

                    width: $contentWidthMm,

                    height: $slotHeightMm,

                );

            }



            if ($ordersPerPage > 1 && $bottomOrder !== null) {

                $mpdf->Line(

                    $marginLeft,

                    $midpointY,

                    $marginLeft + $contentWidthMm,

                    $midpointY,

                );



                $this->writeOrderSlot(

                    mpdf: $mpdf,

                    viewData: $viewData,

                    order: $bottomOrder,

                    x: $marginLeft,

                    y: $midpointY + 1.5,

                    width: $contentWidthMm,

                    height: $slotHeightMm,

                );

            }

        }



        $mpdf->Output($outputAbsolutePath, 'F');



        return count($orderPages);

    }



    /**

     * @param  array<string, mixed>  $viewData

     */

    private function writeOrderSlot(

        Mpdf $mpdf,

        array $viewData,

        OrderPdfOrder $order,

        float $x,

        float $y,

        float $width,

        float $height,

    ): void {

        $html = View::make('pdf.order-detail-slot-content', [

            ...$viewData,

            'order' => $order,

        ])->render();



        $mpdf->WriteFixedPosHTML($html, $x, $y, $width, $height, 'hidden');

    }

}


