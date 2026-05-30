<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Printing\DeliveryLabels;

use App\Contracts\Merchant\Preview\PrintingPreviewPayload;
use App\Enums\PrintingPreviewType;

final readonly class DeliveryLabelPreviewData implements PrintingPreviewPayload
{
    /**
     * @param  list<string>  $addressLines
     */
    public function __construct(
        public string $recipientName,
        public string $courierAddress,
        public array $addressLines,
        public int $addressFontSizePx,
        public ?string $remarks = null,
        public bool $isShrunk = false,
        public ?string $trackingNumber = null,
        public ?string $carrier = null,
    ) {}

    public function type(): PrintingPreviewType
    {
        return PrintingPreviewType::DeliveryLabels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type()->value,
            'recipient_name' => $this->recipientName,
            'courier_address' => $this->courierAddress,
            'address_lines' => $this->addressLines,
            'address_font_size_px' => $this->addressFontSizePx,
            'remarks' => $this->remarks,
            'is_shrunk' => $this->isShrunk,
            'tracking_number' => $this->trackingNumber,
            'carrier' => $this->carrier,
        ];
    }
}
