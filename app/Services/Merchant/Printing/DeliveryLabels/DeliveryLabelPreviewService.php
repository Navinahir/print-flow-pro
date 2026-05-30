<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing\DeliveryLabels;

use App\DTOs\Merchant\Printing\DeliveryLabels\DeliveryLabelPreviewData;

class DeliveryLabelPreviewService
{
    public function __construct(
        private readonly CourierAddressTypographyService $typography,
    ) {}

    public function buildPreview(
        string $recipientName,
        string $courierAddress,
        ?string $remarks = null,
        ?string $trackingNumber = null,
        ?string $carrier = null,
    ): DeliveryLabelPreviewData {
        $fontSizePx = $this->typography->resolveFontSizePx($courierAddress);
        $addressLines = $this->typography->wrapAddressLines($courierAddress);

        return new DeliveryLabelPreviewData(
            recipientName: $recipientName,
            courierAddress: $courierAddress,
            addressLines: $addressLines,
            addressFontSizePx: $fontSizePx,
            remarks: $remarks,
            isShrunk: $fontSizePx < CourierAddressTypographyService::DEFAULT_FONT_SIZE_PX,
            trackingNumber: $trackingNumber,
            carrier: $carrier,
        );
    }
}
