import Alpine from 'alpinejs';
import {
    MERCHANT_STORAGE_KEYS,
    readStorageFlag,
    writeStorageFlag,
} from './storage.js';
import { persistLocalePreference } from './locale.js';

export function registerMerchantShell() {
    Alpine.data('merchantShell', () => ({
        sidebarOpen: false,
        mobileNavOpen: false,
        sidebarCollapsed: false,
        localeSwitching: false,

        init() {
            this.sidebarCollapsed = readStorageFlag(MERCHANT_STORAGE_KEYS.sidebarCollapsed);
            this.localeSwitching = readStorageFlag(MERCHANT_STORAGE_KEYS.localeSwitching);

            this.syncSidebarPendingClass();
            this.syncLocalePendingClass();

            const finishPendingLoaders = () => {
                document.documentElement.classList.remove('merchant-page-loading-pending');

                if (! this.localeSwitching) {
                    return;
                }

                this.localeSwitching = false;
                writeStorageFlag(MERCHANT_STORAGE_KEYS.localeSwitching, false);
                document.documentElement.classList.remove('merchant-locale-switching-pending');
            };

            if (document.readyState === 'complete') {
                requestAnimationFrame(() => finishPendingLoaders.call(this));
            } else {
                window.addEventListener('load', () => finishPendingLoaders.call(this), { once: true });
            }
        },

        toggleSidebar() {
            this.sidebarOpen = ! this.sidebarOpen;
        },

        toggleSidebarCollapse() {
            this.sidebarCollapsed = ! this.sidebarCollapsed;
            writeStorageFlag(MERCHANT_STORAGE_KEYS.sidebarCollapsed, this.sidebarCollapsed);
            this.syncSidebarPendingClass();
        },

        syncSidebarPendingClass() {
            document.documentElement.classList.toggle(
                'merchant-sidebar-collapsed-pending',
                this.sidebarCollapsed,
            );
        },

        syncLocalePendingClass() {
            document.documentElement.classList.toggle(
                'merchant-locale-switching-pending',
                this.localeSwitching,
            );
        },

        startLocaleSwitch() {
            this.localeSwitching = true;
            writeStorageFlag(MERCHANT_STORAGE_KEYS.localeSwitching, true);
            this.syncLocalePendingClass();
        },

        persistLocalePreference(locale) {
            persistLocalePreference(locale);
        },

        openMobileNav() {
            this.mobileNavOpen = true;
        },

        closeMobileNav() {
            this.mobileNavOpen = false;
        },

        toggleMobileNav() {
            this.mobileNavOpen = ! this.mobileNavOpen;
        },
    }));
}

export function startAlpine() {
    window.Alpine = Alpine;
    Alpine.start();
}
