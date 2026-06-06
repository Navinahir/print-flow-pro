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
        public ?string $downloadUrl = null,
        public ?float $sourceWidthMm = null,
        public ?float $sourceHeightMm = null,
        public ?float $outputWidthMm = null,
        public ?float $outputHeightMm = null,
        public ?int $pageNumber = null,
        public ?string $printJobStatus = null,
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
            'download_url' => $this->downloadUrl,
            'source_width_mm' => $this->sourceWidthMm,
            'source_height_mm' => $this->sourceHeightMm,
            'output_width_mm' => $this->outputWidthMm,
            'output_height_mm' => $this->outputHeightMm,
            'page_number' => $this->pageNumber,
            'print_job_status' => $this->printJobStatus,
        ];
    }
}
