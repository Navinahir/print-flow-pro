<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Preview;

use App\Contracts\Merchant\Preview\PrintingPreviewPayload;
use App\Enums\PrintingPreviewType;

final readonly class LogisticsLabelsPreviewData implements PrintingPreviewPayload
{
    public function __construct(
        public string $trackingNumber,
        public string $carrier,
        public string $recipientName,
        public string $recipientAddress,
        public string $shipmentDate,
        public ?string $serviceLevel = null,
    ) {}

    public function type(): PrintingPreviewType
    {
        return PrintingPreviewType::LogisticsLabels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type()->value,
            'tracking_number' => $this->trackingNumber,
            'carrier' => $this->carrier,
            'recipient_name' => $this->recipientName,
            'recipient_address' => $this->recipientAddress,
            'shipment_date' => $this->shipmentDate,
            'service_level' => $this->serviceLevel,
        ];
    }
}
