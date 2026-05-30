const CONTAINER_ID = 'merchant-toast-container';

let container = null;

function ensureContainer() {
    if (container) {
        return container;
    }

    container = document.getElementById(CONTAINER_ID);

    if (! container) {
        container = document.createElement('div');
        container.id = CONTAINER_ID;
        container.className = 'merchant-toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'true');
        document.body.appendChild(container);
    }

    return container;
}

function dismissToast(element) {
    element.classList.add('opacity-0', 'translate-y-2');

    window.setTimeout(() => {
        element.remove();
    }, 300);
}

export function showToast(message, type = 'info', duration = 4000) {
    const root = ensureContainer();
    const toast = document.createElement('div');
    const typeClass = {
        success: 'merchant-toast-success',
        error: 'merchant-toast-error',
        warning: 'merchant-toast-warning',
        info: 'merchant-toast-info',
    }[type] ?? 'merchant-toast-info';

    toast.className = `merchant-toast ${typeClass}`;
    toast.setAttribute('role', 'alert');
    toast.textContent = message;

    root.appendChild(toast);

    window.setTimeout(() => {
        dismissToast(toast);
    }, duration);
}

export function initFlashToasts() {
    const flash = document.getElementById('merchant-flash-data');

    if (! flash) {
        return;
    }

    const success = flash.dataset.success;
    const error = flash.dataset.error;
    const warning = flash.dataset.warning;
    const info = flash.dataset.info;

    if (success) {
        showToast(success, 'success');
    }

    if (error) {
        showToast(error, 'error');
    }

    if (warning) {
        showToast(warning, 'warning');
    }

    if (info) {
        showToast(info, 'info');
    }
}

window.MerchantToast = {
    show: showToast,
};
