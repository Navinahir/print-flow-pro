<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing\DeliveryLabels;

final class CourierCsvHeaderDetector
{
    /**
     * @var list<string>
     */
    private const ADDRESS_HEADERS = [
        'courier_address',
        'delivery_address',
        'shipping_address',
        'recipient_address',
        'address',
        'address_line_1',
        'address1',
        '地址',
        '配送地址',
        '收件地址',
    ];

    /**
     * @var list<string>
     */
    private const REMARKS_HEADERS = [
        'remarks',
        'notes',
        'note',
        'delivery_notes',
        'special_instructions',
        'seller_note',
        'remark_from_buyer',
        '備註',
        '備注',
        '賣家備註',
        '買家備註',
        '備註說明',
    ];

    /**
     * @var list<string>
     */
    private const RECIPIENT_HEADERS = [
        'recipient_name',
        'recipient',
        'consignee',
        'customer_name',
        'name',
        '收件人',
        '收件人姓名',
        '姓名',
        '姓名電話',
        '姓名、電話',
    ];

    /**
     * @var list<string>
     */
    private const TRACKING_HEADERS = [
        'tracking_number',
        'tracking_no',
        'tracking',
        'waybill',
        'shipment_id',
        '追蹤號碼',
        '追蹤編號',
        '物流單號',
    ];

    /**
     * @var list<string>
     */
    private const CARRIER_HEADERS = [
        'carrier',
        'courier',
        'logistics_provider',
        'shipping_carrier',
        '物流商',
        '物流業者',
        '配送方式',
    ];

    /**
     * @param  list<string>  $headers
     */
    public function detectAddressColumn(array $headers): ?string
    {
        return $this->detectColumn($headers, self::ADDRESS_HEADERS);
    }

    /**
     * @param  list<string>  $headers
     */
    public function detectRemarksColumn(array $headers): ?string
    {
        return $this->detectColumn($headers, self::REMARKS_HEADERS);
    }

    /**
     * @param  list<string>  $headers
     */
    public function detectRecipientColumn(array $headers): ?string
    {
        return $this->detectColumn($headers, self::RECIPIENT_HEADERS);
    }

    /**
     * @param  list<string>  $headers
     */
    public function detectTrackingColumn(array $headers): ?string
    {
        return $this->detectColumn($headers, self::TRACKING_HEADERS);
    }

    /**
     * @param  list<string>  $headers
     */
    public function detectCarrierColumn(array $headers): ?string
    {
        return $this->detectColumn($headers, self::CARRIER_HEADERS);
    }

    /**
     * @param  list<string>  $headers
     * @return array{recipient: ?string, address: ?string, remarks: ?string, tracking: ?string, carrier: ?string}
     */
    public function detectColumns(array $headers): array
    {
        return [
            'recipient' => $this->detectRecipientColumn($headers),
            'address' => $this->detectAddressColumn($headers),
            'remarks' => $this->detectRemarksColumn($headers),
            'tracking' => $this->detectTrackingColumn($headers),
            'carrier' => $this->detectCarrierColumn($headers),
        ];
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string>  $candidates
     */
    private function detectColumn(array $headers, array $candidates): ?string
    {
        $candidateSet = array_fill_keys($candidates, true);

        foreach ($headers as $header) {
            $normalized = $this->normalizeHeader($header);

            if (isset($candidateSet[$normalized])) {
                return $header;
            }
        }

        return null;
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = strtolower(trim($header));
        $normalized = preg_replace('/[\s\-]+/', '_', $normalized) ?? $normalized;

        return $normalized;
    }
}
