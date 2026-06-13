@php
    /** @var array<string, mixed>|null $thermalSummary */
    $thermalSummary = $showView['thermal_summary'] ?? null;
@endphp

@if ($thermalSummary)
    <section class="merchant-card merchant-upload-show__section merchant-upload-show__result-card">
        <div class="merchant-upload-show__section-header">
            <h2 class="merchant-upload-show__section-title">{{ __('merchant.uploads.detail.processing_result') }}</h2>
            <p class="merchant-upload-show__section-desc">
                @if (($thermalSummary['merged_single_pdf'] ?? false) && ($thermalSummary['a4_sheets'] ?? 1) > 1)
                    {{ __('merchant.uploads.detail.processing_result_hint_merged_pdf', [
                        'labels' => $thermalSummary['total_labels'],
                        'files' => $thermalSummary['source_files'],
                        'pages' => $thermalSummary['a4_sheets'],
                    ]) }}
                @elseif ($thermalSummary['layout_key'] === 'single')
                    {{ __('merchant.uploads.detail.processing_result_hint_single', [
                        'labels' => $thermalSummary['total_labels'],
                        'files' => $thermalSummary['source_files'],
                        'sheets' => $thermalSummary['a4_sheets'],
                    ]) }}
                @else
                    {{ __('merchant.uploads.detail.processing_result_hint_multi', [
                        'labels' => $thermalSummary['total_labels'],
                        'files' => $thermalSummary['source_files'],
                        'sheets' => $thermalSummary['a4_sheets'],
                    ]) }}
                @endif
            </p>
        </div>
        <div class="merchant-upload-show__result-metrics">
            <div class="merchant-upload-show__metric">
                <span class="merchant-upload-show__metric-value">{{ $thermalSummary['total_labels'] }}</span>
                <span class="merchant-upload-show__metric-label">{{ __('merchant.uploads.detail.labels_count', ['count' => $thermalSummary['total_labels']]) }}</span>
            </div>
            <div class="merchant-upload-show__metric">
                <span class="merchant-upload-show__metric-value">{{ $thermalSummary['a4_sheets'] }}</span>
                <span class="merchant-upload-show__metric-label">
                    @if (($thermalSummary['merged_single_pdf'] ?? false) && ($thermalSummary['a4_sheets'] ?? 1) > 1)
                        {{ __('merchant.uploads.detail.pdf_pages_count', ['count' => $thermalSummary['a4_sheets']]) }}
                    @else
                        {{ __('merchant.uploads.detail.sheets_count', ['count' => $thermalSummary['a4_sheets']]) }}
                    @endif
                </span>
            </div>
            <div class="merchant-upload-show__metric">
                <span class="merchant-upload-show__metric-value">{{ $thermalSummary['output_files'] ?? 1 }}</span>
                <span class="merchant-upload-show__metric-label">{{ trans_choice('merchant.uploads.detail.output_files_count', $thermalSummary['output_files'] ?? 1, ['count' => $thermalSummary['output_files'] ?? 1]) }}</span>
            </div>
        </div>
    </section>
@endif
