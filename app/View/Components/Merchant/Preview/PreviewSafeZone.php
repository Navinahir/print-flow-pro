<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Preview;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PreviewSafeZone extends Component
{
    public string $label;

    public function __construct(
        public int $insetMm = 5,
        public int $widthMm = 150,
        public int $heightMm = 100,
        ?string $label = null,
    ) {
        $this->label = $label ?? (string) __('merchant.preview.safe_zone.aria_label', [
            'inset' => $insetMm,
        ]);
    }

    public function render(): View
    {
        return view('merchant.components.preview.safe-zone');
    }
}
