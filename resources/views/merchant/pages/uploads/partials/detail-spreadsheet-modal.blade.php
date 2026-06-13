<div
    class="merchant-upload-sample-modal"
    x-show="spreadsheetModalOpen"
    x-cloak
    x-on:keydown.escape.window="closeSpreadsheetModal()"
    x-on:click.self="closeSpreadsheetModal()"
    role="dialog"
    aria-modal="true"
    :aria-label="spreadsheetModalLabel"
>
    <div class="merchant-upload-sample-modal__dialog" x-on:click.stop>
        <div class="merchant-upload-sample-modal__header">
            <div class="min-w-0 flex-1">
                <h3 class="truncate text-base font-semibold text-slate-900 dark:text-slate-100" x-text="spreadsheetModalLabel"></h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('merchant.uploads.detail.spreadsheet_preview_hint') }}</p>
            </div>
            <div class="merchant-upload-sample-actions shrink-0">
                <a
                    x-show="spreadsheetModalDownloadUrl"
                    x-cloak
                    class="merchant-upload-sample-icon-btn"
                    :href="spreadsheetModalDownloadUrl"
                    :download="spreadsheetModalLabel"
                    title="{{ __('merchant.uploads.detail.action_download') }}"
                    aria-label="{{ __('merchant.uploads.detail.action_download') }}"
                >
                    @include('merchant.pages.uploads.partials.icons.download')
                </a>
                <button
                    type="button"
                    class="merchant-upload-sample-icon-btn"
                    x-on:click="closeSpreadsheetModal()"
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
            <div class="merchant-upload-sample-modal__csv merchant-upload-sample-modal__csv--spreadsheet">
                <p x-show="spreadsheetModalLoading" x-cloak class="text-sm text-slate-500 dark:text-slate-400">
                    {{ __('merchant.uploads.guides.sample_preview_loading') }}
                </p>
                <p x-show="spreadsheetModalError" x-cloak class="text-sm text-red-600 dark:text-red-400" x-text="spreadsheetModalError"></p>
                <div x-show="! spreadsheetModalLoading && ! spreadsheetModalError && spreadsheetModalHeaders.length" x-cloak class="merchant-upload-sample-modal__table-wrap">
                    <table class="merchant-upload-sample-modal__table">
                        <thead>
                            <tr>
                                <template x-for="(header, index) in spreadsheetModalHeaders" :key="`detail-header-${index}`">
                                    <th x-text="header"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, rowIndex) in spreadsheetModalRows" :key="`detail-row-${rowIndex}`">
                                <tr>
                                    <template x-for="(cell, cellIndex) in row" :key="`detail-cell-${rowIndex}-${cellIndex}`">
                                        <td x-text="cell"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
