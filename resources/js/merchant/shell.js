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

export function registerUploadForm(initialType = '') {
    Alpine.data('uploadForm', () => ({
        type: initialType,
        dragging: false,
        submitting: false,
        fileList: [],

        get accept() {
            if (this.type === 'picking_list') {
                return '.csv,.xlsx,.xls';
            }

            if (this.type) {
                return '.pdf';
            }

            return '.pdf,.csv,.xlsx,.xls';
        },

        handleSelect(event) {
            this.fileList = Array.from(event.target.files);
        },

        handleDrop(event) {
            this.dragging = false;
            const input = document.getElementById('merchant-upload-files');

            if (! input) {
                return;
            }

            input.files = event.dataTransfer.files;
            this.fileList = Array.from(input.files);
        },

        formatSize(bytes) {
            if (bytes < 1024) {
                return `${bytes} B`;
            }

            if (bytes < 1048576) {
                return `${(bytes / 1024).toFixed(1)} KB`;
            }

            return `${(bytes / 1048576).toFixed(1)} MB`;
        },
    }));
}

export function startAlpine() {
    window.Alpine = Alpine;
    Alpine.start();
}
