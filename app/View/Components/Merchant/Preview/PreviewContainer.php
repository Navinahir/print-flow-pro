<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Preview;

use App\Services\Merchant\Preview\PreviewConfigurationService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PreviewContainer extends Component
{
    public int $widthMm;

    public int $heightMm;

    public string $dimensionsLabel;

    public string $ariaLabel;

    public int $safeZoneInsetMm;

    public function __construct(
        ?int $widthMm = null,
        ?int $heightMm = null,
        ?string $ariaLabel = null,
        ?int $safeZoneInsetMm = null,
        public bool $showSafeZone = true,
    ) {
        $configuration = app(PreviewConfigurationService::class)->configuration();

        $this->widthMm = $widthMm ?? (int) $configuration->widthMm;
        $this->heightMm = $heightMm ?? (int) $configuration->heightMm;
        $this->safeZoneInsetMm = $safeZoneInsetMm ?? (int) $configuration->safeZoneInsetMm;
        $this->ariaLabel = $ariaLabel ?? (string) __('merchant.preview.container.aria_label', [
            'width' => $this->widthMm,
            'height' => $this->heightMm,
        ]);
        $this->dimensionsLabel = (string) __('merchant.preview.dimensions_label', [
            'width' => $this->widthMm,
            'height' => $this->heightMm,
        ]);
    }

    public function render(): View
    {
        return view('merchant.components.preview.container');
    }
}
