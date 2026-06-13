@php
    /** @var \App\Models\UploadJob $job */
    /** @var array<string, mixed> $showView */
@endphp

@if ($job->status?->value === 'failed' && $job->error_message)
    <section class="merchant-card merchant-upload-show__section merchant-upload-show__error-card">
        <h2 class="merchant-upload-show__section-title text-red-900 dark:text-red-200">{{ __('merchant.uploads.detail.validation_failed') }}</h2>
        <p class="mt-2 text-sm text-red-800 dark:text-red-300">{{ $job->error_message }}</p>
        <p class="mt-3 text-xs text-red-700 dark:text-red-400">{{ __('merchant.uploads.detail.validation_failed_hint') }}</p>
    </section>
@endif

@if ($job->status?->value === 'completed_with_errors')
    @php
        $failedFileCount = count($showView['failed_source_files'] ?? []);
        if ($failedFileCount === 0 && is_array($job->metadata['file_errors'] ?? null)) {
            $failedFileCount = count($job->metadata['file_errors']);
        }
    @endphp
    <section class="merchant-card merchant-upload-show__section merchant-upload-show__warning-card">
        <h2 class="merchant-upload-show__section-title text-amber-900 dark:text-amber-200">{{ __('merchant.uploads.detail.partial_processing_title') }}</h2>
        <p class="mt-2 text-sm text-amber-800 dark:text-amber-300">{{ __('merchant.uploads.errors.partial_processing_summary', ['count' => $failedFileCount]) }}</p>
        <p class="mt-3 text-xs text-amber-700 dark:text-amber-400">{{ __('merchant.uploads.detail.partial_processing_hint') }}</p>
    </section>
@endif
