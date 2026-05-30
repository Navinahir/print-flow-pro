import Alpine from 'alpinejs';
import { merchantPost } from './ajax.js';
import { MERCHANT_STORAGE_KEYS } from './storage.js';
/**
 * Resolve whether dark mode should be active.
 *
 * @param {string} preference
 * @returns {boolean}
 */
export function resolveDarkMode(preference) {
    if (preference === 'dark') {
        return true;
    }

    if (preference === 'light') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

/**
 * Apply theme preference to the document root.
 *
 * @param {string} preference
 */
export function applyThemePreference(preference) {
    const isDark = resolveDarkMode(preference);
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.dataset.merchantTheme = preference;
}

/**
 * Initialize theme from cookie/localStorage with system fallback.
 */
export function initMerchantTheme() {
    const root = document.getElementById('merchant-app-root');
    const cookieMatch = document.cookie.match(/(?:^|;\s*)merchant_theme=([^;]+)/);
    const stored = localStorage.getItem(MERCHANT_STORAGE_KEYS.theme)
        ?? root?.dataset.themePreference
        ?? (cookieMatch ? decodeURIComponent(cookieMatch[1]) : 'system');

    applyThemePreference(stored);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const preference = localStorage.getItem(MERCHANT_STORAGE_KEYS.theme) ?? stored;

        if (preference === 'system') {
            applyThemePreference('system');
        }
    });
}

/**
 * Register Alpine theme switcher state.
 *
 * @param {string} initialPreference
 */
export function registerMerchantThemeSwitch(initialPreference = 'system') {
    Alpine.data('merchantThemeSwitch', (preference = initialPreference) => ({
        open: false,
        preference,
        effectiveTheme: resolveDarkMode(preference) ? 'dark' : 'light',

        init() {
            const root = document.getElementById('merchant-app-root');
            this.preference = localStorage.getItem(MERCHANT_STORAGE_KEYS.theme)
                ?? root?.dataset.themePreference
                ?? preference;
            applyThemePreference(this.preference);
            this.syncEffectiveTheme();
        },

        syncEffectiveTheme() {
            this.effectiveTheme = resolveDarkMode(this.preference) ? 'dark' : 'light';
        },

        async setTheme(theme) {
            this.preference = theme;
            localStorage.setItem(MERCHANT_STORAGE_KEYS.theme, theme);
            applyThemePreference(theme);
            this.syncEffectiveTheme();
            this.open = false;

            const root = document.getElementById('merchant-app-root');
            const url = root?.dataset.themeUrl ?? '/theme';

            try {
                await merchantPost(url, { theme });
            } catch {
                // localStorage keeps preference when persistence fails.
            }
        },
    }));
}
