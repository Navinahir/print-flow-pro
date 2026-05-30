export const MERCHANT_STORAGE_KEYS = {
    sidebarCollapsed: 'merchant_sidebar_collapsed',
    localeSwitching: 'merchant_locale_switching',
    locale: 'merchant_locale',
    theme: 'merchant_theme',
};

export function readStorageFlag(key) {
    try {
        return localStorage.getItem(key) === '1';
    } catch {
        return false;
    }
}

export function writeStorageFlag(key, active) {
    try {
        if (active) {
            localStorage.setItem(key, '1');
        } else {
            localStorage.removeItem(key);
        }
    } catch {
        // localStorage may be unavailable in restricted contexts.
    }
}

export function applyPendingMerchantLayoutClasses() {
    const root = document.documentElement;

    if (readStorageFlag(MERCHANT_STORAGE_KEYS.sidebarCollapsed)) {
        root.classList.add('merchant-sidebar-collapsed-pending');
    }

    if (readStorageFlag(MERCHANT_STORAGE_KEYS.localeSwitching)) {
        root.classList.add('merchant-locale-switching-pending');
    }
}
