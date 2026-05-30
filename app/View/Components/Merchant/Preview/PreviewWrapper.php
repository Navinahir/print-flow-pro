<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Preview;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PreviewWrapper extends Component
{
    public function __construct(
        public bool $loadingOverlay = true,
    ) {}

    public function render(): View
    {
        return view('merchant.components.preview.wrapper');
    }
}
