<?php

declare(strict_types=1);

namespace App\View\Components\Merchant;

use App\Services\Merchant\LocaleService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LocaleSwitcher extends Component
{
    /**
     * @var list<array{code: string, label: string, is_default: bool}>
     */
    public array $locales;

    public string $currentLocale;

    public function __construct(
        LocaleService $localeService,
    ) {
        $this->locales = $localeService->availableLocales()->all();
        $this->currentLocale = $localeService->current(request());
    }

    public function render(): View
    {
        return view('merchant.components.locale-switcher');
    }
}
