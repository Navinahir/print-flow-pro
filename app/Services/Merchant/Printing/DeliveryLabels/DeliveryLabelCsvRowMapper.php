<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing\DeliveryLabels;

final class DeliveryLabelCsvRowMapper
{
    /**
     * @param  list<string>  $headers
     * @param  list<string>  $row
     * @param  array{recipient: ?string, address: ?string, remarks: ?string, tracking: ?string, carrier: ?string}  $columns
     * @return array{
     *     recipient_name: ?string,
     *     courier_address: ?string,
     *     remarks: ?string,
     *     tracking_number: ?string,
     *     carrier: ?string,
     *     address_line_1: ?string,
     *     metadata: array<string, mixed>
     * }
     */
    public function map(array $headers, array $row, array $columns): array
    {
        $values = $this->associateRow($headers, $row);

        $recipient = $this->valueForColumn($values, $columns['recipient']);
        $address = $this->valueForColumn($values, $columns['address']);
        $remarks = $this->valueForColumn($values, $columns['remarks']);
        $tracking = $this->valueForColumn($values, $columns['tracking']);
        $carrier = $this->valueForColumn($values, $columns['carrier']);

        return [
            'recipient_name' => $recipient,
            'courier_address' => $address,
            'remarks' => $remarks,
            'tracking_number' => $tracking,
            'carrier' => $carrier,
            'address_line_1' => $address,
            'metadata' => [
                'remarks' => $remarks,
                'tracking_number' => $tracking,
                'carrier' => $carrier,
                'raw' => $values,
            ],
        ];
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string>  $row
     * @return array<string, string>
     */
    private function associateRow(array $headers, array $row): array
    {
        $associated = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $associated[$header] = $row[$index] ?? '';
        }

        return $associated;
    }

    /**
     * @param  array<string, string>  $values
     */
    private function valueForColumn(array $values, ?string $column): ?string
    {
        if ($column === null) {
            return null;
        }

        $value = trim($values[$column] ?? '');

        return $value === '' ? null : $value;
    }
}
