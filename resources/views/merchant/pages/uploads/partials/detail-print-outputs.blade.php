@if (! empty($showView['print_outputs']))
    @foreach ($showView['print_outputs'] as $output)
        <section
            class="merchant-card merchant-upload-show__section merchant-upload-show__output-section"
            id="{{ $output['list_id'] }}"
            data-print-output-id="{{ $output['id'] }}"
        >
            <div class="merchant-upload-show__output-card-head">
                <div>
                    <h2 class="merchant-upload-show__section-title" data-output-title>{{ $output['title'] }}</h2>
                    <p class="merchant-upload-show__output-meta">
                        {{ $output['layout_label'] }}
                        ·
                        {{ $output['size_label'] }}
                    </p>
                </div>
                <span class="merchant-upload-show__output-status merchant-upload-show__output-status--{{ $output['status_value'] }}" data-output-status>
                    {{ $output['status_label'] }}
                </span>
            </div>

            <p class="merchant-upload-show__output-sources" data-output-sources>
                <span class="font-medium text-slate-600 dark:text-slate-300">{{ __('merchant.uploads.detail.source_labels_heading') }}:</span>
                {{ $output['source_summary'] }}
            </p>

            <div class="merchant-upload-show__output-actions">
                <button
                    type="button"
                    class="merchant-btn-secondary"
                    @if ($output['preview_url'])
                        x-on:click="openFileModal(@js($output['preview_url']), @js($output['title']), @js($output['download_url']))"
                    @else
                        disabled
                    @endif
                >
                    {{ __('merchant.uploads.detail.action_preview') }}
                </button>
                @if ($output['download_url'])
                    <a href="{{ $output['download_url'] }}" class="merchant-btn-primary" data-output-download>
                        {{ __('merchant.uploads.detail.action_download') }}
                    </a>
                @else
                    <button type="button" class="merchant-btn-primary" disabled data-output-download>
                        {{ __('merchant.uploads.detail.action_download') }}
                    </button>
                @endif
                <button
                    type="button"
                    class="merchant-btn-secondary"
                    @if ($output['preview_url'])
                        x-on:click="printPdfUrl(@js($output['preview_url']))"
                    @else
                        disabled
                    @endif
                >
                    {{ __('merchant.uploads.detail.action_print') }}
                </button>
                @if ($output['can_regenerate'] ?? false)
                    <button
                        type="button"
                        class="merchant-btn-secondary"
                        data-output-regenerate
                        x-on:click="regeneratePrintOutput(@js($output['regenerate_url']), @js($output['list_id']))"
                        :disabled="regeneratingOutputId === @js($output['list_id'])"
                    >
                        <span x-show="regeneratingOutputId !== @js($output['list_id'])">{{ __('merchant.uploads.detail.action_regenerate') }}</span>
                        <span x-show="regeneratingOutputId === @js($output['list_id'])" x-cloak>{{ __('merchant.uploads.detail.regenerating') }}</span>
                    </button>
                @endif
            </div>
        </section>
    @endforeach
@endif
