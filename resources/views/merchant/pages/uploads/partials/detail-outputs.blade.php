@if (! ($showView['use_pdf_preview'] ?? false) && ($uploadPreview->available ?? false))
    <div class="merchant-upload-show__section--wide merchant-upload-show__below-t">
        @include('merchant.pages.uploads.partials.preview-section', [
            'job' => $job,
            'uploadPreview' => $uploadPreview,
            'previewConfig' => $previewConfig,
            'showView' => $showView,
        ])
    </div>
@endif
