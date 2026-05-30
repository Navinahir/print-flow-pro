import Swal from 'sweetalert2';

function labels() {
    const root = document.getElementById('merchant-app-root');

    return {
        confirm: root?.dataset.swalConfirm ?? 'Confirm',
        cancel: root?.dataset.swalCancel ?? 'Cancel',
        ok: root?.dataset.swalOk ?? 'OK',
    };
}

export function confirmDialog(options = {}) {
    const text = labels();

    return Swal.fire({
        icon: options.icon ?? 'warning',
        title: options.title ?? text.confirm,
        text: options.text ?? '',
        html: options.html,
        showCancelButton: options.showCancelButton ?? true,
        confirmButtonText: options.confirmButtonText ?? text.confirm,
        cancelButtonText: options.cancelButtonText ?? text.cancel,
        confirmButtonColor: options.confirmButtonColor ?? '#d97706',
        cancelButtonColor: options.cancelButtonColor ?? '#64748b',
        reverseButtons: true,
        focusCancel: true,
        ...options,
    });
}

export function alertDialog(options = {}) {
    const text = labels();

    return Swal.fire({
        icon: options.icon ?? 'info',
        title: options.title ?? text.ok,
        text: options.text ?? '',
        confirmButtonText: options.confirmButtonText ?? text.ok,
        confirmButtonColor: options.confirmButtonColor ?? '#d97706',
        ...options,
    });
}

export function initDeleteAccountConfirmation() {
    const trigger = document.querySelector('[data-merchant-delete-account]');

    if (! trigger) {
        return;
    }

    const form = document.getElementById('merchant-delete-account-form');

    if (! form) {
        return;
    }

    trigger.addEventListener('click', async () => {
        const result = await confirmDialog({
            title: trigger.dataset.confirmTitle,
            text: trigger.dataset.confirmText,
            input: 'password',
            inputAttributes: {
                autocomplete: 'current-password',
                required: 'true',
            },
            preConfirm: (password) => {
                if (! password) {
                    Swal.showValidationMessage(trigger.dataset.passwordRequired ?? 'Password required');
                }

                return password;
            },
        });

        if (! result.isConfirmed) {
            return;
        }

        const passwordField = form.querySelector('input[name="password"]');

        if (passwordField) {
            passwordField.value = result.value ?? '';
        }

        form.submit();
    });
}

window.MerchantAlert = {
    confirm: confirmDialog,
    alert: alertDialog,
};
