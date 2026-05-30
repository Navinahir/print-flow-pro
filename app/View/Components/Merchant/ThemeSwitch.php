<?php

declare(strict_types=1);

namespace App\View\Components\Merchant;

use App\Services\Merchant\ThemeService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ThemeSwitch extends Component
{
    public string $currentTheme;

    public function __construct(
        ThemeService $themeService,
    ) {
        $this->currentTheme = $themeService->preference(request());
    }

    public function render(): View
    {
        return view('merchant.components.theme-switch');
    }
}
