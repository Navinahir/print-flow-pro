@php
    /** @var \App\DTOs\Merchant\Upload\UploadPreviewResult $uploadPreview */
    /** @var \App\DTOs\Merchant\Preview\PreviewConfiguration $previewConfig */
    /** @var \App\Models\UploadJob $job */
    /** @var array<string, mixed> $showView */
    $previewItems = $uploadPreview->items;
    $usePdfPreview = (bool) ($showView['use_pdf_preview'] ?? false);
    $previewDescription = $usePdfPreview
        ? __('merchant.uploads.preview.description_pdf')
        : __('merchant.uploads.preview.description', [
            'width' => (int) $previewConfig->widthMm,
            'height' => (int) $previewConfig->heightMm,
        ]);
@endphp

<div class="merchant-card merchant-upload-show__preview-card overflow-hidden p-0">
    <x-merchant.preview.wrapper>
        <x-slot:toolbar>
            <x-merchant.preview.toolbar
                :heading="__('merchant.uploads.preview.heading')"
                :description="$previewDescription"
                :print-enabled="! $usePdfPreview"
                :show-safe-zone-toggle="! $usePdfPreview"
            >
                <x-slot:actions>
                    <a
                        x-show="selectedPreview()?.download_url"
                        x-cloak
                        class="merchant-btn-primary"
                        :href="selectedPreview()?.download_url"
                        x-bind:download="true"
                    >
                        {{ __('merchant.print_jobs.actions.download') }}
                    </a>
                    <button
                        type="button"
                        class="merchant-btn-secondary"
                        x-on:click="refreshPreview()"
                        :disabled="previewLoading"
                        :aria-busy="previewLoading"
                    >
                        <span x-show="! previewLoading">{{ __('merchant.uploads.preview.refresh') }}</span>
                        <span x-show="previewLoading" x-cloak>{{ __('merchant.uploads.preview.refreshing') }}</span>
                    </button>
                    @if (! $usePdfPreview)
                        <x-merchant.preview.print-button :enabled="true" />
                    @endif
                </x-slot:actions>
            </x-merchant.preview.toolbar>
        </x-slot:toolbar>

        <div x-show="statusMessage && ! available" x-cloak class="merchant-upload-preview-status px-4 py-3 text-sm">
            <p
                class="rounded-lg px-4 py-3"
                :class="jobStatus === 'failed' ? 'bg-red-50 text-red-800 dark:bg-red-950/40 dark:text-red-200' : 'bg-amber-50 text-amber-900 dark:bg-amber-950/40 dark:text-amber-100'"
                x-text="statusMessage"
            ></p>
        </div>

        <div x-show="error" x-cloak class="merchant-upload-preview-error">
            <p>{{ __('merchant.uploads.preview.error_title') }}</p>
            <p class="text-sm" x-text="error"></p>
            <button type="button" class="merchant-btn-secondary mt-3" x-on:click="refreshPreview()">
                {{ __('merchant.uploads.preview.retry') }}
            </button>
        </div>

        <div x-show="items.length > 1 && available" x-cloak class="border-b border-slate-100 px-4 py-3 dark:border-slate-700">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                {{ __('merchant.uploads.preview.select_label') }}
            </p>
            <div class="merchant-upload-show__sheet-tabs">
                <template x-for="item in items" :key="item.id">
                    <button
                        type="button"
                        class="merchant-upload-show__sheet-tab"
                        :class="selectedId === item.id ? 'merchant-upload-show__sheet-tab--active' : ''"
                        x-on:click="selectItem(item.id)"
                    >
                        <span class="block font-medium" x-text="item.title"></span>
                        <span class="block text-xs opacity-80" x-text="item.subtitle"></span>
                    </button>
                </template>
            </div>
        </div>

        <div x-show="! available && ! previewLoading && ! error && ! statusMessage" x-cloak class="merchant-upload-preview-empty">
            @include('merchant.components.empty-state', [
                'title' => __('merchant.uploads.preview.empty_title'),
                'description' => __('merchant.uploads.preview.empty_description'),
            ])
        </div>

        <div x-show="available" x-cloak class="flex flex-1 flex-col">
            @if ($usePdfPreview)
                <div class="merchant-upload-show__pdf-preview" data-print-area>
                    <p x-show="! selectedPreviewUrl()" x-cloak class="merchant-upload-show__pdf-loading">
                        {{ __('merchant.uploads.preview.pdf_loading') }}
                    </p>
                    <iframe
                        x-show="selectedPreviewUrl()"
                        x-cloak
                        class="merchant-upload-show__pdf-iframe"
                        :src="selectedPreviewUrl()"
                        title="{{ __('merchant.uploads.preview.heading') }}"
                    ></iframe>
                </div>
            @else
                <div data-print-area>
                    <x-merchant.preview.container
                        :width-mm="(int) $previewConfig->widthMm"
                        :height-mm="(int) $previewConfig->heightMm"
                        :safe-zone-inset-mm="(int) $previewConfig->safeZoneInsetMm"
                        :show-safe-zone="false"
                        data-preview-max-zoom="{{ $previewConfig->defaultZoom }}"
                    >
                        @include('merchant.printing.components.previews.preview-content')
                    </x-merchant.preview.container>
                </div>
            @endif
        </div>
    </x-merchant.preview.wrapper>
</div>
