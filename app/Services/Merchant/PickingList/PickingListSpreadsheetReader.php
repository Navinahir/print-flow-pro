<?php

declare(strict_types=1);

namespace App\Services\Merchant\PickingList;

use App\DTOs\Merchant\PickingList\PickingListRow;
use App\Exceptions\Merchant\PickingList\PickingListSpreadsheetException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PickingListSpreadsheetReader
{
    private const REQUIRED_HEADERS = [
        'tracking_number',
        'order_sn',
        'product_info',
    ];

    public function __construct(
        private readonly PickingListProductInfoParser $productInfoParser,
    ) {}

    /**
     * @return list<PickingListRow>
     */
    public function readFile(string $absolutePath, string $sourceFileName): array
    {
        $sheetRows = $this->loadRows($absolutePath);

        if ($sheetRows === []) {
            throw PickingListSpreadsheetException::emptySpreadsheet();
        }

        $headerRow = array_shift($sheetRows);
        $headerMap = $this->mapHeaders($headerRow ?? []);

        $rows = [];
        $lineNumber = 0;

        foreach ($sheetRows as $sheetRow) {
            if ($this->isEmptyRow($sheetRow)) {
                continue;
            }

            $lineNumber++;
            $parsed = $this->productInfoParser->parse($this->cell($sheetRow, $headerMap, 'product_info'));
            $variantColumns = $this->productInfoParser->resolveVariantColumns($parsed);
            $productName = $parsed['product_name'];
            $mainSku = trim($parsed['main_sku']) !== ''
                ? trim($parsed['main_sku'])
                : $productName;

            $rows[] = new PickingListRow(
                lineNumber: $lineNumber,
                trackingNumber: $this->cell($sheetRow, $headerMap, 'tracking_number'),
                orderSn: $this->cell($sheetRow, $headerMap, 'order_sn'),
                mainSku: $mainSku,
                productName: $productName,
                variantSku: $variantColumns['variant_sku'],
                variantName: $variantColumns['variant_name'],
                quantity: max(1, $parsed['quantity']),
                remarkFromBuyer: $this->cell($sheetRow, $headerMap, 'remark_from_buyer'),
                sellerNote: $this->cell($sheetRow, $headerMap, 'seller_note'),
                sourceFileName: $sourceFileName,
                unitPrice: $parsed['unit_price'],
            );
        }

        if ($rows === []) {
            throw PickingListSpreadsheetException::emptySpreadsheet();
        }

        return $rows;
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string>>}
     */
    public function readPreview(string $absolutePath, int $maxRows = 20): array
    {
        $sheetRows = $this->loadRows($absolutePath);

        if ($sheetRows === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(
            static fn (mixed $value): string => trim((string) $value),
            array_shift($sheetRows) ?? [],
        );

        $rows = [];

        foreach ($sheetRows as $sheetRow) {
            if ($this->isEmptyRow($sheetRow)) {
                continue;
            }

            $normalized = [];

            foreach ($headers as $index => $header) {
                $normalized[] = trim((string) ($sheetRow[$index] ?? ''));
            }

            $rows[] = $normalized;

            if (count($rows) >= $maxRows) {
                break;
            }
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @return list<list<mixed>>
     */
    private function loadRows(string $absolutePath): array
    {
        if (! is_readable($absolutePath)) {
            throw PickingListSpreadsheetException::invalidFormat('File is not readable.');
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->loadCsvRows($absolutePath);
        }

        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            throw PickingListSpreadsheetException::invalidFormat('Unsupported file extension.');
        }

        $sheet = IOFactory::load($absolutePath)->getActiveSheet();

        return $sheet->toArray(null, true, true, false);
    }

    /**
     * @return list<list<string>>
     */
    private function loadCsvRows(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'rb');

        if ($handle === false) {
            throw PickingListSpreadsheetException::invalidFormat('Could not open CSV file.');
        }

        $rows = [];

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = array_map(
                    static fn (mixed $value): string => self::stripBom(trim((string) $value)),
                    $row,
                );
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }

    /**
     * @param  list<mixed>  $headerRow
     * @return array<string, int>
     */
    private function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $header) {
            $normalized = strtolower($this->stripBom(trim((string) $header)));

            if ($normalized !== '') {
                $map[$normalized] = (int) $index;
            }
        }

        $missing = array_values(array_filter(
            self::REQUIRED_HEADERS,
            static fn (string $column): bool => ! array_key_exists($column, $map),
        ));

        if ($missing !== []) {
            throw PickingListSpreadsheetException::missingColumns(implode(', ', $missing));
        }

        return $map;
    }

    /**
     * @param  list<mixed>  $row
     * @param  array<string, int>  $headerMap
     */
    private function cell(array $row, array $headerMap, string $column): string
    {
        if (! array_key_exists($column, $headerMap)) {
            return '';
        }

        return trim((string) ($row[$headerMap[$column]] ?? ''));
    }

    /**
     * @param  list<mixed>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private static function stripBom(string $value): string
    {
        if (str_starts_with($value, "\xEF\xBB\xBF")) {
            return substr($value, 3);
        }

        return $value;
    }
}
