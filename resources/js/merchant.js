import './merchant/ajax.js';
import { initFlashToasts } from './merchant/toast.js';
import { initDeleteAccountConfirmation } from './merchant/sweetalert.js';
import { registerMerchantShell, registerUploadForm, startAlpine } from './merchant/shell.js';
import { registerPrintingWorkspace } from './merchant/modules/printing.js';
import { registerUploadPreview } from './merchant/modules/upload-preview.js';
import { registerMerchantPreview } from './merchant/preview/index.js';
import { initMerchantTheme, registerMerchantThemeSwitch } from './merchant/theme.js';
import { bootstrapMerchantThemeFromStorage, initMerchantLocale } from './merchant/locale.js';
import { registerProfilePhotoUpload } from './merchant/profile-photo.js';

bootstrapMerchantThemeFromStorage();

document.addEventListener('DOMContentLoaded', () => {
    initMerchantTheme();
    initMerchantLocale();
    registerMerchantShell();
    registerMerchantThemeSwitch();
    registerProfilePhotoUpload();
    registerPrintingWorkspace();
    registerUploadPreview();
    registerMerchantPreview();

    const uploadRoot = document.getElementById('merchant-upload-form-root');

    if (uploadRoot) {
        registerUploadForm(uploadRoot.dataset.initialType ?? '');
    }

    startAlpine();
    initFlashToasts();
    initDeleteAccountConfirmation();
});
