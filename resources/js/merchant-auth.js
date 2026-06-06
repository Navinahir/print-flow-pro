import './merchant/ajax.js';
import { initFlashToasts } from './merchant/toast.js';
import { registerMerchantShell, startAlpine } from './merchant/shell.js';
import { initMerchantTheme, registerMerchantThemeSwitch } from './merchant/theme.js';
import { bootstrapMerchantThemeFromStorage, initMerchantLocale } from './merchant/locale.js';

bootstrapMerchantThemeFromStorage();

document.addEventListener('DOMContentLoaded', () => {
    initMerchantTheme();
    initMerchantLocale();
    registerMerchantShell();
    registerMerchantThemeSwitch();
    startAlpine();
    initFlashToasts();
});
