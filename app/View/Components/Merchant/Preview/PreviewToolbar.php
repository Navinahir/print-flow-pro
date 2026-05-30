<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Preview;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PreviewToolbar extends Component
{
    public string $heading;

    public string $description;

    public function __construct(
        ?string $heading = null,
        ?string $description = null,
        public bool $printEnabled = false,
        public bool $showSafeZoneToggle = true,
        public bool $requireSelection = false,
    ) {
        $this->heading = $heading ?? (string) __('merchant.preview.toolbar.heading');
        $this->description = $description ?? (string) __('merchant.preview.toolbar.description');
    }

    public function render(): View
    {
        return view('merchant.components.preview.toolbar');
    }
}
