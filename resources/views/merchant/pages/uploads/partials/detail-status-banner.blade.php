@php
    /** @var array<string, mixed> $showView */
    /** @var \App\Models\UploadJob $job */
    $summary = $showView['summary'];
    $statusValue = $summary['status']?->value ?? 'pending';
    $statusClass = match ($statusValue) {
        'completed' => 'merchant-upload-show__status-banner--completed',
        'completed_with_errors' => 'merchant-upload-show__status-banner--warning',
        'failed' => 'merchant-upload-show__status-banner--failed',
        'processing', 'pending' => 'merchant-upload-show__status-banner--processing',
        default => 'merchant-upload-show__status-banner--neutral',
    };
@endphp

<div class="merchant-upload-show__status-banner {{ $statusClass }}">
    <div class="merchant-upload-show__status-banner-top">
        <div class="merchant-upload-show__status-banner-main">
            <p class="merchant-upload-show__status-banner-label">{{ __('merchant.uploads.detail.status') }}</p>
            <div class="merchant-upload-show__status-banner-value">
                @include('merchant.components.upload-status-badge', ['status' => $summary['status']])
            </div>
        </div>
    </div>
    <dl class="merchant-upload-show__status-stats">
        <div>
            <dt>{{ __('merchant.uploads.detail.type') }}</dt>
            <dd>{{ $summary['type_label'] }}</dd>
        </div>
        <div>
            <dt>{{ __('merchant.uploads.detail.file_count') }}</dt>
            <dd>{{ $summary['file_count'] }}</dd>
        </div>
        @if ($showView['picking_summary'] ?? null)
            <div>
                <dt>{{ __('merchant.uploads.detail.processing_result') }}</dt>
                <dd>
                    {{ __('merchant.uploads.detail.layout_picking', ['count' => $showView['picking_summary']['row_count']]) }}
                    @if (($showView['picking_summary']['failed_files'] ?? 0) > 0)
                        · {{ __('merchant.uploads.detail.failed_files_count_short', ['count' => $showView['picking_summary']['failed_files']]) }}
                    @endif
                </dd>
            </div>
        @endif
        @if ($showView['thermal_summary'] ?? null)
            <div>
                <dt>{{ __('merchant.uploads.detail.processing_result') }}</dt>
                <dd>
                    {{ __('merchant.uploads.detail.labels_count', ['count' => $showView['thermal_summary']['total_labels']]) }}
                    @if (($showView['thermal_summary']['merged_single_pdf'] ?? false) && ($showView['thermal_summary']['a4_sheets'] ?? 1) > 1)
                        · {{ trans_choice('merchant.uploads.detail.output_files_count', $showView['thermal_summary']['output_files'] ?? 1, ['count' => $showView['thermal_summary']['output_files'] ?? 1]) }}
                        · {{ __('merchant.uploads.detail.pdf_pages_count', ['count' => $showView['thermal_summary']['a4_sheets']]) }}
                    @else
                        · {{ __('merchant.uploads.detail.sheets_count', ['count' => $showView['thermal_summary']['a4_sheets']]) }}
                    @endif
                </dd>
            </div>
        @endif
        <div>
            <dt>{{ __('merchant.uploads.detail.uploaded_at') }}</dt>
            <dd>{{ $summary['created_at'] ?? __('merchant.general.not_available') }}</dd>
        </div>
    </dl>
</div>
