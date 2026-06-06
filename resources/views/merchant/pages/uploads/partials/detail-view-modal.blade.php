<div
    class="merchant-upload-file-modal"
    x-show="fileModalOpen"
    x-cloak
    x-on:keydown.escape.window="closeFileModal()"
    x-on:click.self="closeFileModal()"
    role="dialog"
    aria-modal="true"
    :aria-label="fileModalLabel"
>
    <div class="merchant-upload-file-modal__dialog" x-on:click.stop>
        <div class="merchant-upload-file-modal__header">
            <div class="min-w-0 flex-1">
                <h3 class="truncate text-base font-semibold text-slate-900 dark:text-slate-100" x-text="fileModalLabel"></h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('merchant.uploads.detail.view_modal_hint') }}</p>
            </div>
            <div class="merchant-upload-show__file-actions shrink-0">
                <a
                    x-show="fileModalDownloadUrl"
                    x-cloak
                    class="merchant-upload-sample-icon-btn"
                    :href="fileModalDownloadUrl"
                    :download="fileModalDownloadName"
                    title="{{ __('merchant.uploads.detail.action_download') }}"
                    aria-label="{{ __('merchant.uploads.detail.action_download') }}"
                >
                    @include('merchant.pages.uploads.partials.icons.download')
                </a>
                <button
                    type="button"
                    class="merchant-upload-sample-icon-btn"
                    x-on:click="closeFileModal()"
                    title="{{ __('merchant.uploads.guides.sample_preview_close') }}"
                    aria-label="{{ __('merchant.uploads.guides.sample_preview_close') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="merchant-upload-file-modal__body">
            <iframe
                x-show="fileModalPreviewUrl"
                x-cloak
                class="merchant-upload-file-modal__iframe"
                :src="fileModalPreviewUrl"
                :title="fileModalLabel"
            ></iframe>
            <p x-show="! fileModalPreviewUrl" x-cloak class="merchant-upload-file-modal__empty">
                {{ __('merchant.uploads.preview.empty_description') }}
            </p>
        </div>
    </div>
</div>
