<?php

declare(strict_types=1);

namespace App\Services\Merchant\Spreadsheet;

use App\DTOs\Merchant\PickingList\PickingListRow;
use App\DTOs\Merchant\Spreadsheet\SpreadsheetBatchParseResult;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use App\Services\Merchant\PickingList\PickingListSpreadsheetReader;
use Throwable;

class SpreadsheetBatchRowParser
{
    public function __construct(
        private readonly PickingListSpreadsheetReader $spreadsheetReader,
    ) {}

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     */
    public function parse(
        array $sourceFiles,
        string $invalidFormatTranslationKey = 'merchant.picking_list.errors.invalid_format',
    ): SpreadsheetBatchParseResult {
        $rows = [];
        $lineNumber = 0;
        $successfulFiles = [];
        $processingResults = [];
        $fileErrors = [];

        foreach ($sourceFiles as $sourceFile) {
            try {
                $parsedRows = $this->spreadsheetReader->readFile(
                    absolutePath: $sourceFile['absolute_path'],
                    sourceFileName: $sourceFile['original_name'],
                );

                foreach ($parsedRows as $parsedRow) {
                    $lineNumber++;
                    $rows[] = new PickingListRow(
                        lineNumber: $lineNumber,
                        trackingNumber: $parsedRow->trackingNumber,
                        orderSn: $parsedRow->orderSn,
                        mainSku: $parsedRow->mainSku,
                        productName: $parsedRow->productName,
                        variantSku: $parsedRow->variantSku,
                        variantName: $parsedRow->variantName,
                        quantity: $parsedRow->quantity,
                        remarkFromBuyer: $parsedRow->remarkFromBuyer,
                        sellerNote: $parsedRow->sellerNote,
                        sourceFileName: $parsedRow->sourceFileName,
                        unitPrice: $parsedRow->unitPrice,
                    );
                }

                $successfulFiles[] = $sourceFile;
                $processingResults[] = [
                    'source_path' => $sourceFile['path'],
                    'status' => 'completed',
                    'error_message' => null,
                ];
            } catch (Throwable $exception) {
                $message = $exception instanceof PdfNormalizationException
                    ? $exception->getMessage()
                    : __($invalidFormatTranslationKey, ['detail' => $exception->getMessage()]);

                $processingResults[] = [
                    'source_path' => $sourceFile['path'],
                    'status' => 'failed',
                    'error_message' => $message,
                ];

                $fileErrors[] = [
                    'source_name' => $sourceFile['original_name'],
                    'source_path' => $sourceFile['path'],
                    'message' => $message,
                ];
            }
        }

        return new SpreadsheetBatchParseResult(
            rows: $rows,
            successfulFiles: $successfulFiles,
            processingResults: $processingResults,
            fileErrors: $fileErrors,
        );
    }
}
