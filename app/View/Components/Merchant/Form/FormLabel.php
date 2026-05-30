<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Form;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormLabel extends Component
{
    public function __construct(
        public string $for = '',
        public bool $required = false,
    ) {}

    public function render(): View
    {
        return view('merchant.components.form.label');
    }
}
