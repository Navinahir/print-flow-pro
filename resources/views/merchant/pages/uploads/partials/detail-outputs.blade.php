@if ($job->status?->value === 'failed' && $job->error_message)
    <section class="merchant-card merchant-upload-show__section merchant-upload-show__error-card merchant-upload-show__section--wide">
        <h2 class="merchant-upload-show__section-title text-red-900 dark:text-red-200">{{ __('merchant.uploads.detail.validation_failed') }}</h2>
        <p class="mt-2 text-sm text-red-800 dark:text-red-300">{{ $job->error_message }}</p>
        <p class="mt-3 text-xs text-red-700 dark:text-red-400">{{ __('merchant.uploads.detail.validation_failed_hint') }}</p>
    </section>
@endif

@if (! ($showView['use_pdf_preview'] ?? false) && ($uploadPreview->available ?? false))
    <div class="merchant-upload-show__section--wide">
        @include('merchant.pages.uploads.partials.preview-section', [
            'job' => $job,
            'uploadPreview' => $uploadPreview,
            'previewConfig' => $previewConfig,
            'showView' => $showView,
        ])
    </div>
@endif
