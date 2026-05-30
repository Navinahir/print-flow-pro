import {
    showAspectRatioSweetAlert,
    validateAspectRatioRemote,
    validateDimensionsLocally,
} from '../preview/aspect-ratio.js';
import { fetchPrintingPreview } from '../preview/preview-fetch.js';

/**
 * Shared Alpine state and methods for printing module workspaces.
 *
 * @param {object} config
 * @returns {object}
 */
export function createPrintingWorkspaceState(config = {}) {
    return {
        module: config.module ?? '',
        listUrl: config.listUrl ?? '',
        validateUrl: config.validateUrl ?? '',
        previewUrl: config.previewUrl ?? '',
        items: config.items ?? [],
        selectedId: config.selectedId ?? null,
        loading: false,
        previewLoading: false,
        safeZoneVisible: config.safeZoneVisible ?? true,
        aspectValidation: null,
        aspectWarningVisible: false,
        forceAdjustment: false,
        labels: config.labels ?? {},

        selectedPreview() {
            return this.selectedItem()?.preview ?? null;
        },

        async refreshSelectedPreview() {
            if (this.previewUrl === '' || this.selectedId === null) {
                return;
            }

            this.previewLoading = true;

            try {
                const preview = await fetchPrintingPreview(
                    this.previewUrl,
                    this.module,
                    this.selectedId,
                );

                if (preview !== null) {
                    const index = this.items.findIndex((item) => item.id === this.selectedId);

                    if (index >= 0) {
                        this.items[index] = {
                            ...this.items[index],
                            preview,
                        };
                    }
                }
            } catch {
                // AJAX errors handled by MerchantAjax interceptor.
            } finally {
                this.previewLoading = false;
            }
        },

        async validateSelectedItem() {
            const item = this.selectedItem();

            if (item === null) {
                return;
            }

            if (item.width && item.height) {
                await this.runAspectValidation(Number(item.width), Number(item.height));

                return;
            }

            if (this.validateUrl === '') {
                return;
            }

            try {
                const data = await validateAspectRatioRemote(this.validateUrl, {
                    width: item.width,
                    height: item.height,
                });
                this.applyAspectValidation(data);
            } catch {
                // AJAX errors handled by MerchantAjax interceptor.
            }
        },

        async runAspectValidation(width, height) {
            if (this.validateUrl !== '') {
                try {
                    const data = await validateAspectRatioRemote(this.validateUrl, { width, height });
                    this.applyAspectValidation(data);

                    return;
                } catch {
                    // Fall through to local validation when offline or error.
                }
            }

            const local = validateDimensionsLocally(width, height);
            this.applyAspectValidation({
                ...local,
                message: local.valid
                    ? this.labels.aspectValid
                    : this.formatInvalidMessage(local.deviation_percent),
            });
        },

        applyAspectValidation(data) {
            this.aspectValidation = data;
            this.updateAspectWarningVisibility();

            if (! data.valid && ! this.forceAdjustment) {
                showAspectRatioSweetAlert(this.labels, data);
            }
        },

        updateAspectWarningVisibility() {
            this.aspectWarningVisible = Boolean(
                this.aspectValidation
                && ! this.aspectValidation.valid
                && ! this.forceAdjustment,
            );
        },

        formatInvalidMessage(deviation) {
            const template = this.labels.aspectInvalid ?? '';

            return template
                .replace(':deviation', Number(deviation).toFixed(1))
                .replace(':tolerance', String(this.aspectValidation?.tolerance_percent ?? 10));
        },

        toggleSafeZone() {
            this.safeZoneVisible = ! this.safeZoneVisible;
        },

        printPreview() {
            import('../preview/print.js').then(({ printPreview }) => {
                printPreview(this.$root);
            });
        },

        selectedItem() {
            if (this.selectedId === null) {
                return null;
            }

            return this.items.find((item) => item.id === this.selectedId) ?? null;
        },
    };
}
