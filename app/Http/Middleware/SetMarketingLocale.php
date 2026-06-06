<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Domains\DomainContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetMarketingLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldApply($request)) {
            return $next($request);
        }

        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        config(['app.locale' => $locale]);

        return $next($request);
    }

    private function shouldApply(Request $request): bool
    {
        $context = app(DomainContext::class);

        if ($context->isMarketing()) {
            return true;
        }

        // Path-only matching must not run on merchant/admin hosts (e.g. tw.xycubic.com/tw).
        if (! config('domains.routing_enabled', true)) {
            return $request->routeIs('marketing.tw', 'marketing.en')
                || $request->is('tw', 'tw/*', 'en', 'en/*');
        }

        return false;
    }

    private function resolveLocale(Request $request): string
    {
        $cookieName = (string) config('marketing.locale_cookie', 'xycubic-marketing-locale');
        $cookieLocale = $request->cookie($cookieName);

        if (is_string($cookieLocale) && $this->isSupportedLocale($cookieLocale)) {
            return $cookieLocale;
        }

        if ($request->routeIs('marketing.en') || $request->is('en', 'en/*')) {
            return 'en';
        }

        if ($request->routeIs('marketing.tw') || $request->is('tw', 'tw/*')) {
            return 'zh-TW';
        }

        return (string) config('marketing.default_locale', 'zh-TW');
    }

    private function isSupportedLocale(string $locale): bool
    {
        return array_key_exists($locale, config('marketing.locales', []));
    }
}
