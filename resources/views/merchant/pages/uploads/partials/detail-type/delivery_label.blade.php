@php
    /** @var array<string, mixed> $showView */
    /** @var \App\Models\UploadJob $job */
@endphp

<div class="merchant-upload-show__t-layout">
    <div class="merchant-upload-show__t-left">
        @include('merchant.pages.uploads.partials.detail-type._source-files', ['showView' => $showView])
        @include('merchant.pages.uploads.partials.detail-type._detail-alerts', ['job' => $job, 'showView' => $showView])
    </div>

    <div class="merchant-upload-show__t-right">
        @include('merchant.pages.uploads.partials.detail-outputs', [
            'job' => $job,
            'showView' => $showView,
            'uploadPreview' => $uploadPreview,
            'previewConfig' => $previewConfig,
        ])
    </div>
</div>

@include('merchant.pages.uploads.partials.detail-spreadsheet-modal')
