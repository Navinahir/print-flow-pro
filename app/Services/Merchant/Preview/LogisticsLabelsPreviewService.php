<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\DTOs\Merchant\Preview\LogisticsLabelsPreviewData;

class LogisticsLabelsPreviewService
{
    public function buildSamplePreview(string $suffix = '1'): LogisticsLabelsPreviewData
    {
        return new LogisticsLabelsPreviewData(
            trackingNumber: (string) __('merchant.printing.preview.logistics_labels.samples.tracking_number', ['id' => $suffix]),
            carrier: (string) __('merchant.printing.preview.logistics_labels.samples.carrier'),
            recipientName: (string) __('merchant.printing.preview.logistics_labels.samples.recipient_name'),
            recipientAddress: (string) __('merchant.printing.preview.logistics_labels.samples.recipient_address'),
            shipmentDate: (string) __('merchant.printing.preview.logistics_labels.samples.shipment_date'),
            serviceLevel: (string) __('merchant.printing.preview.logistics_labels.samples.service_level'),
        );
    }
}
