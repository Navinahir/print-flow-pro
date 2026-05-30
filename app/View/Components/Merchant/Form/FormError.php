<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Form;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormError extends Component
{
    public function __construct(
        public string $name,
        public ?string $bag = null,
    ) {}

    public function render(): View
    {
        return view('merchant.components.form.error');
    }
}
