<?php

declare(strict_types=1);

namespace App\Support;

use App\Support\Domains\DomainContext;

final class CountryScope
{
    public static function currentCountryCode(): ?string
    {
        $fromConfig = config('domains.current.country_code');

        if (is_string($fromConfig) && $fromConfig !== '' && $fromConfig !== '--') {
            return $fromConfig;
        }

        $fromApp = config('app.region_country_code');

        if (is_string($fromApp) && $fromApp !== '' && $fromApp !== '--') {
            return $fromApp;
        }

        return null;
    }

    public static function shouldBypass(): bool
    {
        if (! config('domains.routing_enabled', true)) {
            return true;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return true;
        }

        try {
            $context = app(DomainContext::class);

            return $context->isAdmin() || $context->isMarketing();
        } catch (\Throwable) {
            return true;
        }
    }
}
