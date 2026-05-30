import { initPreviewScaling } from './scale.js';
import { registerMerchantPreviewState } from './safe-zone.js';

let teardown = null;

export function registerMerchantPreview() {
    registerMerchantPreviewState();

    document.addEventListener('DOMContentLoaded', () => {
        refreshMerchantPreview();
    });
}

export function refreshMerchantPreview(root = document) {
    if (typeof teardown === 'function') {
        teardown();
    }

    teardown = initPreviewScaling(root);
}

export { initPreviewScaling, attachPreviewScaling } from './scale.js';
export * from './constants.js';
