import Alpine from 'alpinejs';
import { refreshMerchantPreview } from '../preview/engine.js';
import { printPreview as executePrintPreview } from '../preview/print.js';

/**
 * Shared preview helpers for upload detail pages.
 *
 * @param {object} config
 * @returns {object}
 */
export function createUploadPreviewState(config = {}) {
    return {
        previewUrl: config.previewUrl ?? '',
        uploadId: config.uploadId ?? null,
        preview: config.preview ?? null,
        available: config.available ?? false,
        loading: false,
        previewLoading: false,
        error: null,
        safeZoneVisible: config.safeZoneVisible ?? true,
        selectedId: config.available ? `upload-${config.uploadId}` : null,

        selectedPreview() {
            return this.preview;
        },

        selectedItem() {
            if (! this.available || this.preview === null) {
                return null;
            }

            return {
                id: this.selectedId,
                preview: this.preview,
            };
        },

        toggleSafeZone() {
            this.safeZoneVisible = ! this.safeZoneVisible;
        },

        printPreview() {
            executePrintPreview(this.$root);
        },

        async refreshPreview() {
            if (this.previewUrl === '' || this.uploadId === null) {
                return;
            }

            this.previewLoading = true;
            this.error = null;

            try {
                const response = await window.MerchantAjax.post(this.previewUrl, {});
                const data = response.data ?? response;
                this.preview = data.preview ?? null;
                this.available = Boolean(data.available && this.preview !== null);

                if (! this.available) {
                    this.error = data.message ?? null;
                }

                refreshMerchantPreview(this.$root);
            } catch (error) {
                this.error = error?.response?.data?.message ?? null;
            } finally {
                this.previewLoading = false;
            }
        },
    };
}

export function registerUploadPreview() {
    Alpine.data('uploadPreview', (config = {}) => createUploadPreviewState(config));
}

export { executePrintPreview as printPreview };
