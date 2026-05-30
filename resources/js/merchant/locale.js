import { merchantPost } from './ajax.js';
import { MERCHANT_STORAGE_KEYS } from './storage.js';

/**
 * @param {string} preference
 * @returns {boolean}
 */
function resolveDarkMode(preference) {
    if (preference === 'dark') {
        return true;
    }

    if (preference === 'light') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

/**
 * Apply stored theme preference before paint when possible.
 */
export function bootstrapMerchantThemeFromStorage() {
    try {
        const preference = localStorage.getItem(MERCHANT_STORAGE_KEYS.theme) ?? 'system';
        document.documentElement.classList.toggle('dark', resolveDarkMode(preference));
        document.documentElement.dataset.merchantTheme = preference;
    } catch {
        // localStorage may be unavailable.
    }
}

/**
 * Persist locale preference and sync server session when needed.
 *
 * @param {string} locale
 */
export function persistLocalePreference(locale) {
    try {
        localStorage.setItem(MERCHANT_STORAGE_KEYS.locale, locale);
    } catch {
        // localStorage may be unavailable.
    }
}

/**
 * Sync locale from localStorage to the server when they differ.
 */
export async function initMerchantLocale() {
    const root = document.getElementById('merchant-app-root');

    if (! root?.dataset.currentLocale) {
        return;
    }

    const serverLocale = root.dataset.currentLocale;

    let storedLocale = null;

    try {
        storedLocale = localStorage.getItem(MERCHANT_STORAGE_KEYS.locale);
    } catch {
        return;
    }

    if (! storedLocale) {
        persistLocalePreference(serverLocale);

        return;
    }

    if (storedLocale === serverLocale) {
        return;
    }

    try {
        await merchantPost(root.dataset.localeUrl ?? '/locale', { locale: storedLocale });
        window.location.reload();
    } catch {
        persistLocalePreference(serverLocale);
    }
}
