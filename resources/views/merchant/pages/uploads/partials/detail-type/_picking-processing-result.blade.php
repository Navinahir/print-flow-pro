@php
    /** @var array<string, mixed>|null $pickingSummary */
    $pickingSummary = $showView['picking_summary'] ?? null;
@endphp

@if ($pickingSummary)
    <section class="merchant-card merchant-upload-show__section merchant-upload-show__result-card">
        <div class="merchant-upload-show__section-header">
            <h2 class="merchant-upload-show__section-title">{{ __('merchant.uploads.detail.processing_result') }}</h2>
            <p class="merchant-upload-show__section-desc">
                @if ($pickingSummary['has_partial_errors'] ?? false)
                    {{ __('merchant.uploads.detail.processing_result_hint_picking_partial', [
                        'rows' => $pickingSummary['row_count'],
                        'files' => $pickingSummary['source_files'],
                        'outputs' => $pickingSummary['output_documents'],
                        'failed' => $pickingSummary['failed_files'],
                    ]) }}
                @else
                    {{ __('merchant.uploads.detail.processing_result_hint_picking', [
                        'rows' => $pickingSummary['row_count'],
                        'files' => $pickingSummary['source_files'],
                        'outputs' => $pickingSummary['output_documents'],
                    ]) }}
                @endif
            </p>
        </div>
        <div class="merchant-upload-show__result-metrics">
            <div class="merchant-upload-show__metric">
                <span class="merchant-upload-show__metric-value">{{ $pickingSummary['row_count'] }}</span>
                <span class="merchant-upload-show__metric-label">{{ __('merchant.uploads.detail.layout_picking', ['count' => $pickingSummary['row_count']]) }}</span>
            </div>
            <div class="merchant-upload-show__metric">
                <span class="merchant-upload-show__metric-value">{{ $pickingSummary['total_units'] }}</span>
                <span class="merchant-upload-show__metric-label">{{ __('merchant.printing.preview.picking_list.fields.total_units') }}</span>
            </div>
            <div class="merchant-upload-show__metric">
                <span class="merchant-upload-show__metric-value">{{ $pickingSummary['output_documents'] }}</span>
                <span class="merchant-upload-show__metric-label">{{ __('merchant.uploads.detail.picking_outputs_count') }}</span>
            </div>
            @if (($pickingSummary['failed_files'] ?? 0) > 0)
                <div class="merchant-upload-show__metric">
                    <span class="merchant-upload-show__metric-value">{{ $pickingSummary['failed_files'] }}</span>
                    <span class="merchant-upload-show__metric-label">{{ __('merchant.uploads.detail.failed_files_count') }}</span>
                </div>
            @endif
        </div>
    </section>
@endif
