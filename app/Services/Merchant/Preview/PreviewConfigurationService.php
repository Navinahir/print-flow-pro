<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\DTOs\Merchant\Preview\PreviewConfiguration;
use App\Support\MerchantConfig;

class PreviewConfigurationService
{
    public function configuration(): PreviewConfiguration
    {
        $widthMm = (float) MerchantConfig::get('preview.width_mm', 100);
        $heightMm = (float) MerchantConfig::get('preview.height_mm', 150);

        if ($widthMm <= 0) {
            $widthMm = 100;
        }

        if ($heightMm <= 0) {
            $heightMm = 150;
        }

        $aspectRatio = $heightMm > 0 ? $widthMm / $heightMm : 1.0;

        $safeZoneInsetMm = (float) MerchantConfig::get('preview.safe_zone_inset_mm', 5);
        $defaultZoom = (float) MerchantConfig::get('preview.default_zoom', 1.0);
        $scalingBehavior = (string) MerchantConfig::get('preview.scaling_behavior', 'fit');

        if (! in_array($scalingBehavior, ['fit', 'fill'], true)) {
            $scalingBehavior = 'fit';
        }

        return new PreviewConfiguration(
            widthMm: $widthMm,
            heightMm: $heightMm,
            aspectRatio: $aspectRatio,
            safeZoneInsetMm: max(0, $safeZoneInsetMm),
            defaultZoom: max(0.1, min($defaultZoom, 2.0)),
            scalingBehavior: $scalingBehavior,
        );
    }
}
