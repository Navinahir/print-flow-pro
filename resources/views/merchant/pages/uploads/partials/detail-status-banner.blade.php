@php
    /** @var array<string, mixed> $showView */
    /** @var \App\Models\UploadJob $job */
    $summary = $showView['summary'];
    $statusValue = $summary['status']?->value ?? 'pending';
    $statusClass = match ($statusValue) {
        'completed' => 'merchant-upload-show__status-banner--completed',
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
        @if ($showView['thermal_summary'])
            <div>
                <dt>{{ __('merchant.uploads.detail.processing_result') }}</dt>
                <dd>
                    {{ __('merchant.uploads.detail.labels_count', ['count' => $showView['thermal_summary']['total_labels']]) }}
                    ·
                    {{ __('merchant.uploads.detail.sheets_count', ['count' => $showView['thermal_summary']['a4_sheets']]) }}
                </dd>
            </div>
        @endif
        <div>
            <dt>{{ __('merchant.uploads.detail.uploaded_at') }}</dt>
            <dd>{{ $summary['created_at'] ?? __('merchant.general.not_available') }}</dd>
        </div>
    </dl>
</div>
