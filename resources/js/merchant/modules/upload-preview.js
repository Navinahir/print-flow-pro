import Alpine from 'alpinejs';
import { refreshMerchantPreview } from '../preview/engine.js';
import { printPreview as executePrintPreview } from '../preview/print.js';
import { showToast } from '../toast.js';

/**
 * Shared preview helpers for upload detail pages.
 *
 * @param {object} config
 * @returns {object}
 */
export function createUploadPreviewState(config = {}) {
    return {
        previewUrl: config.previewUrl ?? '',
        uploadId: config.uploadId ?? null,
        preview: config.preview ?? null,
        available: config.available ?? false,
        items: config.items ?? [],
        selectedId: config.selectedId ?? null,
        statusMessage: config.statusMessage ?? null,
        jobStatus: config.jobStatus ?? null,
        pollWhileProcessing: config.pollWhileProcessing ?? false,
        usePdfPreview: config.usePdfPreview ?? false,
        loading: false,
        previewLoading: false,
        error: null,
        pollTimer: null,
        fileModalOpen: false,
        fileModalPreviewUrl: '',
        fileModalDownloadUrl: '',
        fileModalDownloadName: '',
        fileModalLabel: '',
        printFrame: null,
        regeneratingOutputId: null,
        spreadsheetModalOpen: false,
        spreadsheetModalLabel: '',
        spreadsheetModalDownloadUrl: '',
        spreadsheetModalHeaders: [],
        spreadsheetModalRows: [],
        spreadsheetModalLoading: false,
        spreadsheetModalError: null,

        init() {
            if (this.pollWhileProcessing) {
                this.startPolling();
            }
        },

        destroy() {
            this.stopPolling();
            this.removePrintFrame();
            this.setRegeneratingOverlay(false);
        },

        selectedPreview() {
            if (this.items.length > 0) {
                const item = this.items.find((entry) => entry.id === this.selectedId);

                return item?.preview ?? this.preview;
            }

            return this.preview;
        },

        selectedPreviewUrl() {
            if (this.items.length > 0) {
                const item = this.items.find((entry) => entry.id === this.selectedId);

                return item?.preview_url ?? item?.preview?.preview_url ?? null;
            }

            return this.preview?.preview_url ?? null;
        },

        selectedItem() {
            if (! this.available || this.preview === null) {
                return null;
            }

            return {
                id: this.selectedId,
                preview: this.selectedPreview(),
            };
        },

        openFileModal(previewUrl, label, downloadUrl = '') {
            this.fileModalPreviewUrl = previewUrl ?? '';
            this.fileModalLabel = label ?? '';
            this.fileModalDownloadUrl = downloadUrl ?? '';
            this.fileModalDownloadName = label ?? '';
            this.fileModalOpen = true;
            document.body.classList.add('merchant-upload-file-modal-open');
        },

        closeFileModal() {
            this.fileModalOpen = false;
            this.fileModalPreviewUrl = '';
            this.fileModalDownloadUrl = '';
            this.fileModalDownloadName = '';
            this.fileModalLabel = '';
            document.body.classList.remove('merchant-upload-file-modal-open');
        },

        async openSpreadsheetPreview(previewUrl, label, downloadUrl = '') {
            this.spreadsheetModalLabel = label ?? '';
            this.spreadsheetModalDownloadUrl = downloadUrl ?? '';
            this.spreadsheetModalHeaders = [];
            this.spreadsheetModalRows = [];
            this.spreadsheetModalError = null;
            this.spreadsheetModalLoading = true;
            this.spreadsheetModalOpen = true;
            document.body.classList.add('merchant-upload-sample-modal-open');

            try {
                const response = await fetch(previewUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('preview_failed');
                }

                const payload = await response.json();
                this.spreadsheetModalHeaders = Array.isArray(payload.headers) ? payload.headers : [];
                this.spreadsheetModalRows = Array.isArray(payload.rows) ? payload.rows : [];
            } catch {
                this.spreadsheetModalError = window.__merchantUploadSamplePreview?.csvError
                    ?? 'Could not load preview.';
            } finally {
                this.spreadsheetModalLoading = false;
            }
        },

        closeSpreadsheetModal() {
            this.spreadsheetModalOpen = false;
            this.spreadsheetModalLabel = '';
            this.spreadsheetModalDownloadUrl = '';
            this.spreadsheetModalHeaders = [];
            this.spreadsheetModalRows = [];
            this.spreadsheetModalError = null;
            this.spreadsheetModalLoading = false;
            document.body.classList.remove('merchant-upload-sample-modal-open');
        },

        printPreview() {
            if (this.usePdfPreview) {
                const url = this.selectedPreviewUrl();

                if (url) {
                    this.printPdfUrl(url);
                }

                return;
            }

            executePrintPreview(this.$root);
        },

        printPdfUrl(url, label = '') {
            if (! url) {
                return;
            }

            this.removePrintFrame();

            if (label) {
                this.fileModalLabel = label;
            }

            const printWindow = window.open(url, '_blank');

            if (printWindow) {
                const attemptPrint = () => {
                    try {
                        printWindow.focus();
                        printWindow.print();
                    } catch {
                        // PDF viewer may not be ready yet.
                    }
                };

                printWindow.addEventListener('load', () => {
                    window.setTimeout(attemptPrint, 300);
                });

                window.setTimeout(attemptPrint, 1200);

                return;
            }

            this.printPdfUrlViaFrame(url);
        },

        printPdfUrlViaFrame(url) {
            const frame = document.createElement('iframe');
            frame.className = 'merchant-upload-show__print-frame';
            frame.src = url;
            frame.title = this.fileModalLabel || 'Print preview';
            frame.setAttribute('aria-hidden', 'true');

            const attemptPrint = () => {
                try {
                    frame.contentWindow?.focus();
                    frame.contentWindow?.print();
                } catch {
                    showToast(
                        window.__merchantUploadPreview?.printBlocked
                            ?? 'Could not open the print dialog. Please use Preview, then print from the PDF viewer.',
                        'warning',
                    );
                }
            };

            frame.onload = () => {
                window.setTimeout(attemptPrint, 500);
            };

            document.body.appendChild(frame);
            this.printFrame = frame;

            window.setTimeout(attemptPrint, 2000);
            window.setTimeout(() => this.removePrintFrame(), 120000);
        },

        removePrintFrame() {
            if (this.printFrame) {
                this.printFrame.remove();
                this.printFrame = null;
            }
        },

        setRegeneratingOverlay(active) {
            document.body.classList.toggle('merchant-upload-regenerating', active);
        },

        async regeneratePrintOutput(url, listId) {
            if (! url || this.regeneratingOutputId !== null) {
                return;
            }

            this.regeneratingOutputId = listId;
            this.setRegeneratingOverlay(true);

            let shouldReload = false;

            try {
                const response = await window.MerchantAjax.post(url);
                const data = response.data ?? response;

                if (data.message) {
                    showToast(data.message, 'success');
                }

                shouldReload = true;
                window.location.reload();
            } catch {
                // MerchantAjax shows toast
            } finally {
                if (! shouldReload) {
                    this.regeneratingOutputId = null;
                    this.setRegeneratingOverlay(false);
                }
            }
        },

        async selectItem(id) {
            this.selectedId = id;
            await this.refreshPreview(id);
        },

        startPolling() {
            this.stopPolling();
            this.pollTimer = window.setInterval(() => {
                this.refreshPreview(this.selectedId, true);
            }, 3000);
        },

        stopPolling() {
            if (this.pollTimer !== null) {
                window.clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        async refreshPreview(itemId = null, silent = false) {
            if (this.previewUrl === '' || this.uploadId === null) {
                return;
            }

            if (! silent) {
                this.previewLoading = true;
            }

            this.error = null;

            try {
                const payload = {};

                if (itemId ?? this.selectedId) {
                    payload.item_id = itemId ?? this.selectedId;
                }

                const response = await window.MerchantAjax.post(this.previewUrl, payload);
                const data = response.data ?? response;
                this.preview = data.preview ?? null;
                this.available = Boolean(data.available && this.preview !== null);
                this.items = data.items ?? this.items;
                this.selectedId = data.selected_item_id ?? this.selectedId;
                this.statusMessage = data.status_message ?? null;

                if (data.available === false && this.statusMessage) {
                    this.available = false;
                }

                if (this.available) {
                    this.stopPolling();
                    this.jobStatus = 'completed';
                }

                if (! this.available && ! this.statusMessage) {
                    this.error = data.message ?? null;
                }

                refreshMerchantPreview(this.$root);
            } catch (error) {
                if (! silent) {
                    this.error = error?.response?.data?.message ?? null;
                }
            } finally {
                if (! silent) {
                    this.previewLoading = false;
                }
            }
        },
    };
}

export function registerUploadPreview() {
    Alpine.data('uploadPreview', (config = {}) => createUploadPreviewState(config));
}

export { executePrintPreview as printPreview };
