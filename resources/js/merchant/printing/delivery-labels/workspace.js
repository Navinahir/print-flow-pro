import Alpine from 'alpinejs';

import { refreshMerchantPreview } from '../../preview/engine.js';
import { createPrintingWorkspaceState } from '../../modules/printing-workspace-shared.js';
import { fitAddressFontSize, observeDeliveryLabelLayout } from './layout.js';
import {
    confirmCsvUpload,
    showCsvImportSuccess,
    uploadDeliveryLabelCsv,
} from './csv-upload.js';

export function registerDeliveryLabelsWorkspace() {
    Alpine.data('deliveryLabelsWorkspace', (config = {}) => ({
        ...createPrintingWorkspaceState(config),
        csvUploadUrl: config.csvUploadUrl ?? '',
        typography: config.typography ?? {},
        fittedFontSizePx: null,
        layoutTeardown: null,
        csvUploading: false,

        async selectItem(id) {
            this.selectedId = id;
            this.forceAdjustment = false;
            this.aspectValidation = null;
            this.aspectWarningVisible = false;
            this.fittedFontSizePx = null;
            this.loading = true;

            try {
                await this.validateSelectedItem();
                await this.refreshSelectedPreview();
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    this.refreshPreviewAndLayout();
                });
            }
        },

        async uploadCsvFile(file) {
            if (! file || this.csvUploadUrl === '') {
                return;
            }

            const confirmed = await confirmCsvUpload(this.labels);

            if (! confirmed.isConfirmed) {
                return;
            }

            this.csvUploading = true;

            try {
                const formData = new FormData();
                formData.append('file', file);

                const data = await uploadDeliveryLabelCsv(this.csvUploadUrl, formData);

                if (Array.isArray(data.items) && data.items.length > 0) {
                    this.items = data.items;
                    this.selectedId = data.items[0].id;
                    showCsvImportSuccess(this.labels, data.message);
                    await this.selectItem(this.selectedId);
                }
            } catch {
                // AJAX errors handled by MerchantAjax interceptor.
            } finally {
                this.csvUploading = false;
            }
        },

        handleCsvInputChange(event) {
            const file = event.target.files?.[0] ?? null;

            if (file) {
                this.uploadCsvFile(file);
            }

            event.target.value = '';
        },

        refreshPreviewAndLayout() {
            const root = document.getElementById('merchant-printing-workspace') ?? document;
            refreshMerchantPreview(root);

            this.$nextTick(() => {
                const preview = root.querySelector('[data-delivery-label-preview]');

                if (preview) {
                    this.refreshDeliveryLabelLayout(preview);
                }
            });
        },

        refreshDeliveryLabelLayout(root) {
            if (typeof this.layoutTeardown === 'function') {
                this.layoutTeardown();
                this.layoutTeardown = null;
            }

            const preview = this.selectedPreview();

            if (! preview) {
                return;
            }

            const initialFontSize = Number(preview.address_font_size_px ?? this.typography.defaultFontSizePx ?? 18);
            this.fittedFontSizePx = fitAddressFontSize(root, initialFontSize);

            this.layoutTeardown = observeDeliveryLabelLayout(root, () => {
                this.fittedFontSizePx = fitAddressFontSize(root, initialFontSize);
            });
        },

        addressFontSizePx() {
            return this.fittedFontSizePx
                ?? this.selectedPreview()?.address_font_size_px
                ?? this.typography.defaultFontSizePx
                ?? 18;
        },
    }));
}
