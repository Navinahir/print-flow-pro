import Alpine from 'alpinejs';

import { refreshMerchantPreview } from '../preview/engine.js';
import { createPrintingWorkspaceState } from './printing-workspace-shared.js';

export function registerPrintingWorkspace() {
    Alpine.data('printingWorkspace', (config = {}) => ({
        ...createPrintingWorkspaceState(config),

        async selectItem(id) {
            this.selectedId = id;
            this.forceAdjustment = false;
            this.aspectValidation = null;
            this.aspectWarningVisible = false;
            this.loading = true;

            try {
                await this.validateSelectedItem();
                await this.refreshSelectedPreview();
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    refreshMerchantPreview(document.getElementById('merchant-printing-workspace') ?? document);
                });
            }
        },
    }));
}
