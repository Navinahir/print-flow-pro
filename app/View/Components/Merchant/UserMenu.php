<?php

declare(strict_types=1);

namespace App\View\Components\Merchant;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserMenu extends Component
{
    public function render(): View
    {
        return view('merchant.components.user-menu');
    }
}
