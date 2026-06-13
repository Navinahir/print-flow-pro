@php
    /** @var array<string, mixed>|null $orderSummary */
    $orderSummary = $showView['order_summary'] ?? null;
@endphp

@if ($orderSummary)
    <section class="merchant-card merchant-upload-show__section merchant-upload-show__result-card">
        <div class="merchant-upload-show__section-header">
            <h2 class="merchant-upload-show__section-title">{{ __('merchant.uploads.detail.processing_result') }}</h2>
            <p class="merchant-upload-show__section-desc">
                {{ __('merchant.uploads.detail.processing_result_hint_order', [
                    'pages' => $orderSummary['total_pages'],
                    'files' => $orderSummary['source_files'],
                ]) }}
            </p>
        </div>
        <div class="merchant-upload-show__result-metrics">
            <div class="merchant-upload-show__metric">
                <span class="merchant-upload-show__metric-value">{{ $orderSummary['total_pages'] }}</span>
                <span class="merchant-upload-show__metric-label">{{ __('merchant.uploads.detail.layout_merge', ['count' => $orderSummary['total_pages']]) }}</span>
            </div>
            <div class="merchant-upload-show__metric">
                <span class="merchant-upload-show__metric-value">{{ $orderSummary['source_files'] }}</span>
                <span class="merchant-upload-show__metric-label">{{ __('merchant.uploads.detail.file_count') }}</span>
            </div>
        </div>
    </section>
@endif
