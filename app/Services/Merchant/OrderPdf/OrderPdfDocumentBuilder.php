<?php



declare(strict_types=1);



namespace App\Services\Merchant\OrderPdf;



use App\DTOs\Merchant\OrderPdf\OrderPdfDocument;

use App\DTOs\Merchant\OrderPdf\OrderPdfLineItem;

use App\DTOs\Merchant\OrderPdf\OrderPdfOrder;

use App\DTOs\Merchant\PickingList\PickingListRow;



class OrderPdfDocumentBuilder

{

    /**

     * @param  list<PickingListRow>  $rows

     * @param  list<string>  $sourceFileNames

     */

    public function build(array $rows, array $sourceFileNames): OrderPdfDocument

    {

        $rowsByFile = [];



        foreach ($rows as $row) {

            $rowsByFile[$row->sourceFileName][] = $row;

        }



        $orders = [];

        $processedFiles = [];



        foreach ($sourceFileNames as $sourceFileName) {

            if (! isset($rowsByFile[$sourceFileName])) {

                continue;

            }



            $processedFiles[] = $sourceFileName;

            array_push($orders, ...$this->buildOrdersForFile($rowsByFile[$sourceFileName]));

        }



        foreach ($rowsByFile as $sourceFileName => $fileRows) {

            if (in_array($sourceFileName, $processedFiles, true)) {

                continue;

            }



            array_push($orders, ...$this->buildOrdersForFile($fileRows));

        }



        return new OrderPdfDocument(

            orders: $orders,

            sourceFiles: $sourceFileNames,

        );

    }



    /**

     * @param  list<PickingListRow>  $rows

     * @return list<OrderPdfOrder>

     */

    private function buildOrdersForFile(array $rows): array

    {

        $ordersBySn = [];

        $orderSequence = [];



        foreach ($rows as $row) {

            $orderSn = trim($row->orderSn);



            if ($orderSn === '') {

                continue;

            }



            if (! array_key_exists($orderSn, $ordersBySn)) {

                $ordersBySn[$orderSn] = [

                    'buyer_note' => trim($row->remarkFromBuyer),

                    'line_items' => [],

                ];

                $orderSequence[] = $orderSn;

            } elseif ($ordersBySn[$orderSn]['buyer_note'] === '' && trim($row->remarkFromBuyer) !== '') {

                $ordersBySn[$orderSn]['buyer_note'] = trim($row->remarkFromBuyer);

            }



            $quantity = max(1, $row->quantity);

            $lineTotal = $row->unitPrice > 0 ? $row->unitPrice * $quantity : 0;



            $ordersBySn[$orderSn]['line_items'][] = new OrderPdfLineItem(

                lineNumber: count($ordersBySn[$orderSn]['line_items']) + 1,

                mainSku: $row->mainSku,

                productName: $row->productName,

                variantSku: $row->variantSku,

                variantName: $row->variantName,

                quantity: $quantity,

                lineTotal: $lineTotal,

            );

        }



        $orders = [];



        foreach ($orderSequence as $orderSn) {

            $orderData = $ordersBySn[$orderSn];

            $lineItems = $orderData['line_items'];



            if ($lineItems === []) {

                continue;

            }



            $orders[] = new OrderPdfOrder(

                orderSn: $orderSn,

                packageNumber: 1,

                buyerNote: $orderData['buyer_note'],

                lineItems: $lineItems,

            );

        }



        return $orders;

    }

}


