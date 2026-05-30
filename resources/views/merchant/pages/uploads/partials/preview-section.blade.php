@php
    /** @var \App\DTOs\Merchant\Upload\UploadPreviewResult $uploadPreview */
    /** @var \App\DTOs\Merchant\Preview\PreviewConfiguration $previewConfig */
@endphp

<div
    class="merchant-card overflow-hidden p-0"
    x-data="uploadPreview({
        previewUrl: @js(route('uploads.preview.show', $job)),
        uploadId: @js($job->id),
        preview: @js($uploadPreview->preview),
        available: @js($uploadPreview->available),
        safeZoneVisible: true,
    })"
>
    <x-merchant.preview.wrapper>
        <x-slot:toolbar>
            <x-merchant.preview.toolbar
                :heading="__('merchant.uploads.preview.heading')"
                :description="__('merchant.uploads.preview.description', ['width' => (int) $previewConfig->widthMm, 'height' => (int) $previewConfig->heightMm])"
                :print-enabled="true"
            >
                <x-slot:actions>
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
                    <x-merchant.preview.print-button :enabled="true" />
                </x-slot:actions>
            </x-merchant.preview.toolbar>
        </x-slot:toolbar>

        <div x-show="error" x-cloak class="merchant-upload-preview-error">
            <p>{{ __('merchant.uploads.preview.error_title') }}</p>
            <p class="text-sm" x-text="error"></p>
            <button type="button" class="merchant-btn-secondary mt-3" x-on:click="refreshPreview()">
                {{ __('merchant.uploads.preview.retry') }}
            </button>
        </div>

        <div x-show="! available && ! previewLoading && ! error" x-cloak class="merchant-upload-preview-empty">
            @include('merchant.components.empty-state', [
                'title' => __('merchant.uploads.preview.empty_title'),
                'description' => __('merchant.uploads.preview.empty_description'),
            ])
        </div>

        <div x-show="available" x-cloak class="flex flex-1 flex-col">
            <div data-print-area>
                <x-merchant.preview.container
                    :width-mm="(int) $previewConfig->widthMm"
                    :height-mm="(int) $previewConfig->heightMm"
                    :safe-zone-inset-mm="(int) $previewConfig->safeZoneInsetMm"
                    data-preview-max-zoom="{{ $previewConfig->defaultZoom }}"
                >
                    @include('merchant.printing.components.previews.preview-content')
                </x-merchant.preview.container>
            </div>
        </div>
    </x-merchant.preview.wrapper>
</div>
