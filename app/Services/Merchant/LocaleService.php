<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\Models\DomainSetting;
use App\Services\Domain\DomainConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Cookie;

class LocaleService
{
    public const SESSION_KEY = 'merchant_locale';

    public const COOKIE_KEY = 'merchant_locale';

    public function __construct(
        private readonly DomainConfigurationService $domainConfiguration,
    ) {}

    /**
     * @return Collection<int, array{code: string, label: string, is_default: bool}>
     */
    public function availableLocales(): Collection
    {
        $config = $this->domainConfiguration->current();

        if ($config === null) {
            return collect([
                ['code' => 'en', 'label' => 'English', 'is_default' => true],
            ]);
        }

        $setting = DomainSetting::query()
            ->where('region_key', $config->regionKey)
            ->with('locales')
            ->first();

        if ($setting === null || $setting->locales->isEmpty()) {
            return collect([
                ['code' => $config->defaultLocale, 'label' => $config->defaultLocale, 'is_default' => true],
            ]);
        }

        return $setting->locales->map(fn ($locale): array => [
            'code' => $locale->locale,
            'label' => $locale->label,
            'is_default' => (bool) $locale->is_default,
        ])->values();
    }

    public function current(Request $request): string
    {
        $preferred = $request->session()->get(self::SESSION_KEY);

        if (is_string($preferred) && $this->isSupported($preferred)) {
            return $preferred;
        }

        $cookie = $request->cookie(self::COOKIE_KEY);

        if (is_string($cookie) && $this->isSupported($cookie)) {
            return $cookie;
        }

        $config = $this->domainConfiguration->current();

        return $config?->defaultLocale ?? (string) config('app.locale', 'en');
    }

    public function isSupported(string $locale): bool
    {
        return $this->availableLocales()->contains(
            fn (array $entry): bool => $entry['code'] === $locale,
        );
    }

    public function apply(Request $request, string $locale): void
    {
        if (! $this->isSupported($locale)) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $locale);
        app()->setLocale($locale);
        config(['app.locale' => $locale]);
    }

    public function makePreferenceCookie(string $locale): Cookie
    {
        return cookie(
            name: self::COOKIE_KEY,
            value: $locale,
            minutes: 60 * 24 * 365,
            path: '/',
            secure: config('session.secure', false),
            httpOnly: false,
            sameSite: 'lax',
        );
    }
}
