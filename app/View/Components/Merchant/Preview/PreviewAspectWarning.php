<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Preview;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PreviewAspectWarning extends Component
{
    public function render(): View
    {
        return view('merchant.components.preview.aspect-warning');
    }
}
