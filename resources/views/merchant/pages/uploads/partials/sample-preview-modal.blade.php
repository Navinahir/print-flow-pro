<div
    class="merchant-upload-sample-modal"
    x-show="samplePreviewOpen"
    x-cloak
    x-on:keydown.escape.window="closeSamplePreview()"
    x-on:click.self="closeSamplePreview()"
    role="dialog"
    aria-modal="true"
    :aria-label="samplePreviewLabel"
>
    <div class="merchant-upload-sample-modal__dialog" x-on:click.stop>
        <div class="merchant-upload-sample-modal__header">
            <div class="min-w-0 flex-1">
                <h3 class="truncate text-base font-semibold text-slate-900 dark:text-slate-100" x-text="samplePreviewLabel"></h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('merchant.uploads.guides.sample_preview_modal_hint') }}</p>
            </div>
            <div class="merchant-upload-sample-actions shrink-0">
                <a
                    x-show="samplePreviewUrl"
                    x-cloak
                    class="merchant-upload-sample-icon-btn"
                    :href="samplePreviewUrl"
                    :download="samplePreviewDownloadName"
                    title="{{ __('merchant.uploads.guides.sample_download') }}"
                    aria-label="{{ __('merchant.uploads.guides.sample_download') }}"
                >
                    @include('merchant.pages.uploads.partials.icons.download')
                </a>
                <button
                    type="button"
                    class="merchant-upload-sample-icon-btn"
                    x-on:click="closeSamplePreview()"
                    title="{{ __('merchant.uploads.guides.sample_preview_close') }}"
                    aria-label="{{ __('merchant.uploads.guides.sample_preview_close') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="merchant-upload-sample-modal__body">
            <template x-if="samplePreviewKind === 'pdf'">
                <iframe
                    class="merchant-upload-sample-modal__iframe"
                    :src="samplePreviewUrl"
                    :title="samplePreviewLabel"
                ></iframe>
            </template>

            <template x-if="samplePreviewKind === 'csv'">
                <div class="merchant-upload-sample-modal__csv">
                    <p x-show="samplePreviewLoading" x-cloak class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('merchant.uploads.guides.sample_preview_loading') }}
                    </p>
                    <p x-show="samplePreviewError" x-cloak class="text-sm text-red-600 dark:text-red-400" x-text="samplePreviewError"></p>
                    <pre x-show="! samplePreviewLoading && ! samplePreviewError && samplePreviewCsvText" x-cloak class="merchant-upload-sample-modal__csv-pre" x-text="samplePreviewCsvText"></pre>
                </div>
            </template>

            <template x-if="samplePreviewKind === 'none'">
                <div class="merchant-upload-sample-modal__empty">
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('merchant.uploads.guides.sample_preview_unavailable') }}</p>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    window.__merchantUploadSamplePreview = {
        csvError: @js(__('merchant.uploads.guides.sample_preview_error')),
    };
</script>
