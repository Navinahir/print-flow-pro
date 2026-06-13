import Alpine from 'alpinejs';

export function registerUploadForm(initialType = '') {
    Alpine.data('uploadForm', () => ({
        type: initialType,
        dragging: false,
        submitting: false,
        fileList: [],
        combinedOutput: true,
        samplePreviewOpen: false,
        samplePreviewUrl: '',
        samplePreviewLabel: '',
        samplePreviewKind: 'none',
        samplePreviewDownloadName: '',
        samplePreviewCsvText: '',
        samplePreviewTableHeaders: [],
        samplePreviewTableRows: [],
        samplePreviewLoading: false,
        samplePreviewError: null,
        samplePreviewEndpoint: window.__merchantUploadSamplePreview?.endpoint ?? '/uploads/samples/preview',

        get accept() {
            if (this.type === 'picking_list' || this.type === 'order_pdf') {
                return '.csv,.xlsx,.xls';
            }

            if (this.type === 'delivery_label') {
                return '.pdf,.csv,.xlsx,.xls';
            }

            if (this.type === 'thermal_label') {
                return '.pdf';
            }

            return '.pdf,.csv,.xlsx,.xls';
        },

        handleSelect(event) {
            this.fileList = Array.from(event.target.files);
        },

        handleDrop(event) {
            this.dragging = false;
            const input = document.getElementById('merchant-upload-files');

            if (! input) {
                return;
            }

            input.files = event.dataTransfer.files;
            this.fileList = Array.from(input.files);
        },

        formatSize(bytes) {
            if (bytes < 1024) {
                return `${bytes} B`;
            }

            if (bytes < 1048576) {
                return `${(bytes / 1024).toFixed(1)} KB`;
            }

            return `${(bytes / 1048576).toFixed(1)} MB`;
        },

        async openSamplePreview(url, label, kind, downloadName, assetPath = '') {
            this.samplePreviewUrl = url;
            this.samplePreviewLabel = label;
            this.samplePreviewKind = kind;
            this.samplePreviewDownloadName = downloadName;
            this.samplePreviewCsvText = '';
            this.samplePreviewTableHeaders = [];
            this.samplePreviewTableRows = [];
            this.samplePreviewError = null;
            this.samplePreviewLoading = kind === 'csv' || kind === 'spreadsheet';
            this.samplePreviewOpen = true;
            document.body.classList.add('merchant-upload-sample-modal-open');

            if (kind === 'csv') {
                try {
                    const response = await fetch(url);

                    if (! response.ok) {
                        throw new Error('preview_failed');
                    }

                    this.samplePreviewCsvText = await response.text();
                } catch {
                    this.samplePreviewError = window.__merchantUploadSamplePreview?.csvError ?? null;
                } finally {
                    this.samplePreviewLoading = false;
                }

                return;
            }

            if (kind === 'spreadsheet') {
                if (! assetPath) {
                    this.samplePreviewLoading = false;
                    this.samplePreviewError = window.__merchantUploadSamplePreview?.csvError ?? null;

                    return;
                }

                try {
                    const endpoint = new URL(this.samplePreviewEndpoint, window.location.origin);
                    endpoint.searchParams.set('path', assetPath);
                    const response = await fetch(endpoint.toString(), {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (! response.ok) {
                        throw new Error('preview_failed');
                    }

                    const payload = await response.json();
                    this.samplePreviewTableHeaders = Array.isArray(payload.headers) ? payload.headers : [];
                    this.samplePreviewTableRows = Array.isArray(payload.rows) ? payload.rows : [];
                } catch {
                    this.samplePreviewError = window.__merchantUploadSamplePreview?.csvError ?? null;
                } finally {
                    this.samplePreviewLoading = false;
                }
            }
        },

        closeSamplePreview() {
            this.samplePreviewOpen = false;
            this.samplePreviewUrl = '';
            this.samplePreviewLabel = '';
            this.samplePreviewKind = 'none';
            this.samplePreviewDownloadName = '';
            this.samplePreviewCsvText = '';
            this.samplePreviewTableHeaders = [];
            this.samplePreviewTableRows = [];
            this.samplePreviewError = null;
            this.samplePreviewLoading = false;
            document.body.classList.remove('merchant-upload-sample-modal-open');
        },
    }));
}
