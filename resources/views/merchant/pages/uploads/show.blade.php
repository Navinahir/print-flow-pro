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
    <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-2">
        <div class="space-y-6">
            <div class="merchant-card">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500 dark:text-slate-400">{{ __('merchant.uploads.detail.type') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $job->type?->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500 dark:text-slate-400">{{ __('merchant.uploads.detail.status') }}</dt>
                        <dd class="mt-1">
                            @include('merchant.components.upload-status-badge', ['status' => $job->status])
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500 dark:text-slate-400">{{ __('merchant.uploads.detail.uploaded_by') }}</dt>
                        <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $job->uploadedBy?->name ?? __('merchant.general.not_available') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500 dark:text-slate-400">{{ __('merchant.uploads.detail.file_count') }}</dt>
                        <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $job->file_count }}</dd>
                    </div>
                </dl>
            </div>

            @if ($job->pdfUploads->isNotEmpty())
                <div class="merchant-card">
                    <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('merchant.uploads.detail.pdf_files') }}</h2>
                    <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($job->pdfUploads as $file)
                            <li class="flex items-center justify-between py-3 text-sm">
                                <span class="truncate text-slate-700 dark:text-slate-300">{{ $file->original_name }}</span>
                                <span class="shrink-0 text-slate-500 dark:text-slate-400">{{ number_format($file->size_bytes / 1024, 1) }} KB</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (! empty($job->metadata['spreadsheet_files']))
                <div class="merchant-card">
                    <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('merchant.uploads.detail.spreadsheet_files') }}</h2>
                    <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($job->metadata['spreadsheet_files'] as $file)
                            <li class="flex items-center justify-between py-3 text-sm">
                                <span class="truncate text-slate-700 dark:text-slate-300">{{ $file['original_name'] }}</span>
                                <span class="shrink-0 text-slate-500 dark:text-slate-400">{{ number_format(($file['size_bytes'] ?? 0) / 1024, 1) }} KB</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="min-h-[24rem] lg:min-h-0">
            @include('merchant.pages.uploads.partials.preview-section', [
                'job' => $job,
                'uploadPreview' => $uploadPreview,
                'previewConfig' => $previewConfig,
            ])
        </div>
    </div>
@endsection
