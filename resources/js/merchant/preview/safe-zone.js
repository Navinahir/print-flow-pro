import Alpine from 'alpinejs';

/**
 * Shared Alpine state for preview wrapper (safe zone toggle, loading).
 * Used standalone on PreviewWrapper; printing modules extend printingWorkspace instead.
 */
export function registerMerchantPreviewState() {
    Alpine.data('merchantPreview', (config = {}) => ({
        safeZoneVisible: config.safeZoneVisible ?? true,
        loading: config.loading ?? false,

        toggleSafeZone() {
            this.safeZoneVisible = ! this.safeZoneVisible;
        },
    }));
}

export const defaultSafeZoneVisible = true;

export const defaultSafeZoneInsetMm = 5;
