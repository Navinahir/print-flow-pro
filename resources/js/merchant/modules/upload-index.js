import Alpine from 'alpinejs';
import { confirmDialog } from '../sweetalert.js';
import { showToast } from '../toast.js';

export function registerUploadIndex() {
    Alpine.data('uploadIndex', () => ({
        deletingId: null,

        async deleteUpload(url, label) {
            if (this.deletingId !== null) {
                return;
            }

            const result = await confirmDialog({
                title: window.__merchantUploadIndex?.deleteTitle ?? 'Delete upload?',
                text: window.__merchantUploadIndex?.deleteText ?? '',
                confirmButtonText: window.__merchantUploadIndex?.deleteConfirm ?? 'Delete',
            });

            if (! result.isConfirmed) {
                return;
            }

            this.deletingId = url;

            try {
                await window.MerchantAjax.delete(url, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                showToast(window.__merchantUploadIndex?.deleteSuccess ?? 'Upload deleted.', 'success');

                document
                    .querySelectorAll(`[data-upload-row="${label}"]`)
                    .forEach((row) => row.remove());

                if (document.querySelectorAll('[data-upload-row]').length === 0) {
                    window.location.reload();
                }
            } catch {
                // MerchantAjax shows toast
            } finally {
                this.deletingId = null;
            }
        },
    }));
}
