@if (! empty($showView['print_outputs']))
    <div class="merchant-upload-show__outputs-wrap">
        <div class="merchant-upload-show__outputs-header">
            <h3 class="merchant-upload-show__output-panel-title">{{ __('merchant.uploads.detail.print_outputs') }}</h3>
            <p class="merchant-upload-show__section-desc">{{ $showView['print_outputs_hint'] ?? __('merchant.uploads.detail.print_outputs_hint') }}</p>
        </div>

        <div class="merchant-upload-show__output-grid--cards merchant-upload-show__output-grid--stack">
            @foreach ($showView['print_outputs'] as $output)
                <section
                    class="merchant-card merchant-upload-show__section merchant-upload-show__output-section merchant-upload-show__output-card"
                    id="{{ $output['list_id'] }}"
                    data-print-output-id="{{ $output['id'] }}"
                >
                    <div class="merchant-upload-show__output-card-head">
                        <div class="merchant-upload-show__output-card-intro">
                            <h3 class="merchant-upload-show__output-title" data-output-title>{{ $output['title'] }}</h3>
                        </div>
                        <span class="merchant-upload-show__output-status merchant-upload-show__output-status--{{ $output['status_value'] }}" data-output-status>
                            {{ $output['status_label'] }}
                        </span>
                    </div>

                    <div class="merchant-upload-show__output-stats">
                        @if (($output['output_kind'] ?? '') === 'thermal')
                            <span class="merchant-upload-show__output-stat">
                                {{ $output['layout_label'] }}
                            </span>
                            @if (($output['page_count'] ?? 1) > 1)
                                <span class="merchant-upload-show__output-stat">
                                    {{ __('merchant.uploads.detail.pdf_pages_count', ['count' => $output['page_count']]) }}
                                </span>
                            @endif
                        @elseif (($output['output_kind'] ?? '') === 'picking')
                            <span class="merchant-upload-show__output-stat">
                                {{ __('merchant.uploads.detail.layout_picking', ['count' => $output['row_count'] ?? 0]) }}
                            </span>
                        @elseif (($output['output_kind'] ?? '') === 'order')
                            <span class="merchant-upload-show__output-stat">
                                {{ __('merchant.uploads.detail.layout_merge', ['count' => $output['order_count'] ?? 0]) }}
                            </span>
                        @endif
                        <span class="merchant-upload-show__output-stat merchant-upload-show__output-stat--muted">
                            {{ $output['size_label'] }}
                        </span>
                    </div>

                    @if (! empty($output['source_groups']))
                        <div class="merchant-upload-show__output-sources" data-output-sources>
                            <p class="merchant-upload-show__output-sources-label">
                                {{ $output['source_heading'] ?? __('merchant.uploads.detail.source_labels_heading') }}
                            </p>
                            <ul class="merchant-upload-show__source-group-list">
                                @foreach ($output['source_groups'] as $group)
                                    <li class="merchant-upload-show__source-group-item">
                                        <span class="merchant-upload-show__source-group-name" title="{{ $group['name'] }}">
                                            {{ $group['name'] }}
                                        </span>
                                        <span class="merchant-upload-show__source-group-count">
                                            @if (($group['page_count'] ?? 0) === 1)
                                                {{ __('merchant.uploads.detail.source_group_one_label') }}
                                            @else
                                                {{ __('merchant.uploads.detail.source_group_labels', ['count' => $group['page_count']]) }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif (! empty($output['source_summary']))
                        <p class="merchant-upload-show__output-sources merchant-upload-show__output-sources--compact" data-output-sources>
                            <span class="merchant-upload-show__output-sources-label">
                                {{ $output['source_heading'] ?? __('merchant.uploads.detail.source_labels_heading') }}
                            </span>
                            {{ $output['source_summary'] }}
                        </p>
                    @endif

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
                                x-on:click="printPdfUrl(@js($output['preview_url']), @js($output['title']))"
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
                                :disabled="regeneratingOutputId !== null"
                            >
                                <span x-show="regeneratingOutputId !== @js($output['list_id'])">{{ __('merchant.uploads.detail.action_regenerate') }}</span>
                                <span x-show="regeneratingOutputId === @js($output['list_id'])" x-cloak>{{ __('merchant.uploads.detail.regenerating') }}</span>
                            </button>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endif
