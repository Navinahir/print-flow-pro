<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class ThemeService
{
    public const COOKIE_KEY = 'merchant_theme';

    public const PREFERENCE_LIGHT = 'light';

    public const PREFERENCE_DARK = 'dark';

    public const PREFERENCE_SYSTEM = 'system';

    /**
     * @return list<string>
     */
    public function supportedPreferences(): array
    {
        return [
            self::PREFERENCE_LIGHT,
            self::PREFERENCE_DARK,
            self::PREFERENCE_SYSTEM,
        ];
    }

    public function preference(Request $request): string
    {
        $cookie = $request->cookie(self::COOKIE_KEY);

        if (is_string($cookie) && in_array($cookie, $this->supportedPreferences(), true)) {
            return $cookie;
        }

        return self::PREFERENCE_SYSTEM;
    }

    public function isDarkMode(Request $request): bool
    {
        return $this->preference($request) === self::PREFERENCE_DARK;
    }

    public function makePreferenceCookie(string $preference): Cookie
    {
        return cookie(
            name: self::COOKIE_KEY,
            value: $preference,
            minutes: 60 * 24 * 365,
            path: '/',
            secure: config('session.secure', false),
            httpOnly: false,
            sameSite: 'lax',
        );
    }

    public function isValidPreference(string $preference): bool
    {
        return in_array($preference, $this->supportedPreferences(), true);
    }
}
