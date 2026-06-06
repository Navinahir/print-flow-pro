@extends('merchant.layouts.app')

@section('title', __('merchant.uploads.show_title', ['id' => $job->id]))

@section('breadcrumbs')
    @include('merchant.components.breadcrumb', [
        'items' => [
            ['label' => __('merchant.nav.uploads'), 'url' => route('uploads.index')],
            ['label' => __('merchant.uploads.show_title', ['id' => $job->id]), 'active' => true],
        ],
    ])
@endsection

@section('page-header')
    @component('merchant.components.page-header', [
        'title' => __('merchant.uploads.show_title', ['id' => $job->id]),
        'subtitle' => __('merchant.uploads.show_subtitle'),
    ])
        @slot('actions')
            <a href="{{ route('uploads.index') }}" class="merchant-btn-secondary">
                {{ __('merchant.uploads.back_to_history') }}
            </a>
        @endslot
    @endcomponent
@endsection

@section('content')
    <div
        class="merchant-upload-show w-full"
        x-data="uploadPreview({
            previewUrl: @js(route('uploads.preview.show', $job)),
            uploadId: @js($job->id),
            preview: @js($uploadPreview->preview),
            available: @js($uploadPreview->available),
            items: @js($uploadPreview->items),
            selectedId: @js($uploadPreview->selectedItemId ?? ($uploadPreview->items[0]['id'] ?? null)),
            statusMessage: @js($uploadPreview->statusMessage),
            jobStatus: @js($job->status?->value),
            pollWhileProcessing: @js(in_array($job->status?->value, ['pending', 'processing'], true)),
            usePdfPreview: @js($showView['use_pdf_preview']),
        })"
    >
        @include('merchant.pages.uploads.partials.detail-status-banner', ['showView' => $showView, 'job' => $job])

        <div class="merchant-upload-show__columns">
            <div class="merchant-upload-show__column merchant-upload-show__column--left">
                @include('merchant.pages.uploads.partials.detail-source-files', ['job' => $job, 'showView' => $showView])
            </div>

            <div class="merchant-upload-show__column merchant-upload-show__column--right">
                @include('merchant.pages.uploads.partials.detail-processing-result', ['showView' => $showView])
                @include('merchant.pages.uploads.partials.detail-print-outputs', ['showView' => $showView])
            </div>
        </div>

        @include('merchant.pages.uploads.partials.detail-outputs', [
            'job' => $job,
            'showView' => $showView,
            'uploadPreview' => $uploadPreview,
            'previewConfig' => $previewConfig,
        ])

        @include('merchant.pages.uploads.partials.detail-view-modal')
    </div>
@endsection
