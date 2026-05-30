<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Preview;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PrintButton extends Component
{
    public function __construct(
        public bool $enabled = true,
        public bool $requireSelection = false,
    ) {}

    public function render(): View
    {
        return view('merchant.components.preview.print-button');
    }
}
