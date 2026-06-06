<?php

declare(strict_types=1);

namespace App\View\Composers;

use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class MarketingComposer
{
    public function compose(View $view): void
    {
        $scheme = request()->secure() ? 'https' : 'http';
        $merchantHost = (string) config('domains.fallback_merchants.tw.domain', 'localhost:8001');

        $view->with([
            'merchantRegisterUrl' => "{$scheme}://{$merchantHost}/register",
            'marketingHomeUrl' => $this->marketingHomeUrl(),
        ]);
    }

    private function marketingHomeUrl(): string
    {
        $locale = app()->getLocale();
        $routeName = config("marketing.locales.{$locale}.route");

        if (is_string($routeName) && Route::has($routeName)) {
            return route($routeName);
        }

        return url($locale === 'en' ? '/en' : '/tw');
    }
}
