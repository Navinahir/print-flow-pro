@php
    /** @var array<string, mixed> $showView */
    $sourceFiles = $showView['source_files'] ?? [];
@endphp

@if (! empty($sourceFiles))
    <section class="merchant-card merchant-upload-show__section">
        <div class="merchant-upload-show__section-header">
            <h2 class="merchant-upload-show__section-title">{{ $showView['source_files_heading'] ?? __('merchant.uploads.detail.pdf_files') }}</h2>
            @if (! empty($showView['source_files_hint']))
                <p class="merchant-upload-show__section-desc">{{ $showView['source_files_hint'] }}</p>
            @endif
        </div>
        <ul class="merchant-upload-show__file-list">
            @foreach ($sourceFiles as $file)
                <li class="merchant-upload-show__file-item">
                    <div
                        class="merchant-upload-show__file-icon {{ ($file['file_kind'] ?? 'pdf') === 'spreadsheet' ? 'merchant-upload-show__file-icon--sheet' : '' }}"
                        aria-hidden="true"
                    >{{ $file['icon'] ?? 'PDF' }}</div>
                    <div class="merchant-upload-show__file-meta min-w-0 flex-1">
                        <p class="merchant-upload-show__file-name">{{ $file['name'] }}</p>
                        <p class="merchant-upload-show__file-size">{{ number_format($file['size_kb'], 1) }} KB</p>
                        @if (! empty($file['processing_status_label']))
                            <p class="merchant-upload-show__file-status merchant-upload-show__file-status--{{ $file['processing_status'] }}">
                                {{ $file['processing_status_label'] }}
                            </p>
                        @endif
                        @if (! empty($file['error_message']))
                            <p class="merchant-upload-show__file-error">{{ $file['error_message'] }}</p>
                        @endif
                    </div>
                    <div class="merchant-upload-show__file-actions shrink-0">
                        <button
                            type="button"
                            class="merchant-upload-sample-icon-btn"
                            title="{{ __('merchant.uploads.detail.action_view') }}"
                            aria-label="{{ __('merchant.uploads.detail.action_view') }}"
                            @if (! empty($file['preview_url']))
                                x-on:click="openFileModal(@js($file['preview_url']), @js($file['name']), @js($file['download_url']))"
                            @elseif (! empty($file['spreadsheet_preview_url']))
                                x-on:click="openSpreadsheetPreview(@js($file['spreadsheet_preview_url']), @js($file['name']), @js($file['download_url']))"
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
