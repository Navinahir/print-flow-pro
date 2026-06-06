@php
    /** @var \App\Models\UploadJob $job */
    /** @var array<string, mixed> $showView */
    $sourceFiles = $showView['source_files'] ?? [];
@endphp

@if (! empty($sourceFiles))
    <section class="merchant-card merchant-upload-show__section">
        <div class="merchant-upload-show__section-header">
            <h2 class="merchant-upload-show__section-title">{{ __('merchant.uploads.detail.pdf_files') }}</h2>
            <p class="merchant-upload-show__section-desc">{{ __('merchant.uploads.detail.pdf_files_hint') }}</p>
        </div>
        <ul class="merchant-upload-show__file-list">
            @foreach ($sourceFiles as $file)
                <li class="merchant-upload-show__file-item">
                    <div class="merchant-upload-show__file-icon" aria-hidden="true">PDF</div>
                    <div class="merchant-upload-show__file-meta min-w-0 flex-1">
                        <p class="merchant-upload-show__file-name">{{ $file['name'] }}</p>
                        <p class="merchant-upload-show__file-size">{{ number_format($file['size_kb'], 1) }} KB</p>
                    </div>
                    <div class="merchant-upload-show__file-actions shrink-0">
                        <button
                            type="button"
                            class="merchant-upload-sample-icon-btn"
                            title="{{ __('merchant.uploads.detail.action_view') }}"
                            aria-label="{{ __('merchant.uploads.detail.action_view') }}"
                            @if ($file['preview_url'])
                                x-on:click="openFileModal(@js($file['preview_url']), @js($file['name']), @js($file['download_url']))"
                            @else
                                disabled
                            @endif
                        >
                            @include('merchant.pages.uploads.partials.icons.eye')
                        </button>
                        @if ($file['download_url'])
                            <a
                                href="{{ $file['download_url'] }}"
                                class="merchant-upload-sample-icon-btn"
                                title="{{ __('merchant.uploads.detail.action_download') }}"
                                aria-label="{{ __('merchant.uploads.detail.action_download') }}"
                                download
                            >
                                @include('merchant.pages.uploads.partials.icons.download')
                            </a>
                        @else
                            <button
                                type="button"
                                class="merchant-upload-sample-icon-btn"
                                title="{{ __('merchant.uploads.detail.action_download') }}"
                                aria-label="{{ __('merchant.uploads.detail.action_download') }}"
                                disabled
                            >
                                @include('merchant.pages.uploads.partials.icons.download')
                            </button>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif

@if (! empty($job->metadata['spreadsheet_files']))
    <section class="merchant-card merchant-upload-show__section">
        <div class="merchant-upload-show__section-header">
            <h2 class="merchant-upload-show__section-title">{{ __('merchant.uploads.detail.spreadsheet_files') }}</h2>
        </div>
        <ul class="merchant-upload-show__file-list">
            @foreach ($job->metadata['spreadsheet_files'] as $file)
                <li class="merchant-upload-show__file-item">
                    <div class="merchant-upload-show__file-icon merchant-upload-show__file-icon--sheet" aria-hidden="true">XLS</div>
                    <div class="merchant-upload-show__file-meta">
                        <p class="merchant-upload-show__file-name">{{ $file['original_name'] }}</p>
                        <p class="merchant-upload-show__file-size">{{ number_format(($file['size_bytes'] ?? 0) / 1024, 1) }} KB</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif
