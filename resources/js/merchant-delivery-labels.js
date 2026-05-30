import './merchant/ajax.js';
import { initFlashToasts } from './merchant/toast.js';
import { initDeleteAccountConfirmation } from './merchant/sweetalert.js';
import { registerMerchantShell, registerUploadForm, startAlpine } from './merchant/shell.js';
import { registerMerchantPreview } from './merchant/preview/index.js';
import { registerDeliveryLabelsWorkspace } from './merchant/printing/delivery-labels/index.js';
import { initMerchantTheme, registerMerchantThemeSwitch } from './merchant/theme.js';

document.addEventListener('DOMContentLoaded', () => {
    initMerchantTheme();
    registerMerchantShell();
    registerMerchantThemeSwitch();
    registerDeliveryLabelsWorkspace();
    registerMerchantPreview();

    const uploadRoot = document.getElementById('merchant-upload-form-root');

    if (uploadRoot) {
        registerUploadForm(uploadRoot.dataset.initialType ?? '');
    }

    startAlpine();
    initFlashToasts();
    initDeleteAccountConfirmation();
});
