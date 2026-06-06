import Alpine from 'alpinejs';

export function registerUploadForm(initialType = '') {
    Alpine.data('uploadForm', () => ({
        type: initialType,
        dragging: false,
        submitting: false,
        fileList: [],
        thermalCombinedOutput: true,
        samplePreviewOpen: false,
        samplePreviewUrl: '',
        samplePreviewLabel: '',
        samplePreviewKind: 'none',
        samplePreviewDownloadName: '',
        samplePreviewCsvText: '',
        samplePreviewLoading: false,
        samplePreviewError: null,

        get accept() {
            if (this.type === 'picking_list') {
                return '.csv,.xlsx,.xls';
            }

            if (this.type === 'delivery_label') {
                return '.pdf,.csv,.xlsx,.xls';
            }

            if (this.type === 'order_pdf' || this.type === 'thermal_label') {
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

        async openSamplePreview(url, label, kind, downloadName) {
            this.samplePreviewUrl = url;
            this.samplePreviewLabel = label;
            this.samplePreviewKind = kind;
            this.samplePreviewDownloadName = downloadName;
            this.samplePreviewCsvText = '';
            this.samplePreviewError = null;
            this.samplePreviewLoading = kind === 'csv';
            this.samplePreviewOpen = true;
            document.body.classList.add('merchant-upload-sample-modal-open');

            if (kind !== 'csv') {
                return;
            }

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
        },

        closeSamplePreview() {
            this.samplePreviewOpen = false;
            this.samplePreviewUrl = '';
            this.samplePreviewLabel = '';
            this.samplePreviewKind = 'none';
            this.samplePreviewDownloadName = '';
            this.samplePreviewCsvText = '';
            this.samplePreviewError = null;
            this.samplePreviewLoading = false;
            document.body.classList.remove('merchant-upload-sample-modal-open');
        },
    }));
}
