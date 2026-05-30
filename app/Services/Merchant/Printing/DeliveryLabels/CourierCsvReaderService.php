<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing\DeliveryLabels;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class CourierCsvReaderService
{
    /**
     * @return array{headers: list<string>, rows: list<list<string>>}
     */
    public function read(UploadedFile $file): array
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read uploaded CSV file.');
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open uploaded CSV file.');
        }

        try {
            $headers = fgetcsv($handle);

            if ($headers === false) {
                return ['headers' => [], 'rows' => []];
            }

            $headers = array_map(
                static fn (mixed $header): string => trim((string) $header),
                $headers,
            );

            $rows = [];

            while (($row = fgetcsv($handle)) !== false) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $rows[] = array_map(
                    static fn (mixed $value): string => trim((string) $value),
                    $row,
                );
            }

            return [
                'headers' => $headers,
                'rows' => $rows,
            ];
        } finally {
            fclose($handle);
        }
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
}
