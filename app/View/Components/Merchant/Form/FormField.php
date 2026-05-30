<?php

declare(strict_types=1);

namespace App\View\Components\Merchant\Form;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormField extends Component
{
    public function __construct(
        public string $name,
        public ?string $bag = null,
        public bool $required = false,
        public ?string $label = null,
        public ?string $labelFor = null,
    ) {}

    public function render(): View
    {
        return view('merchant.components.form.field');
    }
}
