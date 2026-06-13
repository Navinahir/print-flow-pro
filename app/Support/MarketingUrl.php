<?php

declare(strict_types=1);

namespace App\Support;

use App\Services\Domain\DomainConfigurationService;
use Illuminate\Http\Request;

final class MarketingUrl
{
    public static function home(?string $locale = null): string
    {
        /** @var Request $request */
        $request = request();

        $scheme = $request->getScheme();
        $host = app(DomainConfigurationService::class)->effectiveMarketingHost();

        if ($host === '') {
            $host = (string) config('domains.fallback_infrastructure.marketing.host', 'localhost:8000');
        }

        $path = self::pathForLocale($locale ?? app()->getLocale());

        return "{$scheme}://{$host}{$path}";
    }

    private static function pathForLocale(string $locale): string
    {
        $normalized = str_replace('-', '_', $locale);
        $routeName = config("marketing.locales.{$normalized}.route");

        return $routeName === 'marketing.en' ? '/en' : '/tw';
    }
}
