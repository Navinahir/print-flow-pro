@extends('merchant.layouts.app')

@section('title', __('merchant.uploads.title'))

@section('breadcrumbs')
    @include('merchant.components.breadcrumb', [
        'items' => [
            ['label' => __('merchant.nav.uploads'), 'url' => route('uploads.index')],
            ['label' => __('merchant.uploads.title'), 'active' => true],
        ],
    ])
@endsection

@section('page-header')
    @component('merchant.components.page-header', [
        'title' => __('merchant.uploads.title'),
        'subtitle' => __('merchant.uploads.subtitle'),
    ])
        @slot('actions')
            @can('create', \App\Models\UploadJob::class)
                <a href="{{ route('uploads.create') }}" class="merchant-btn-primary">
                    {{ __('merchant.uploads.new_upload') }}
                </a>
            @endcan
        @endslot
    @endcomponent
@endsection

@section('content')
    @if ($jobs->isEmpty())
        @component('merchant.components.empty-state', [
            'title' => __('merchant.uploads.empty.title'),
            'description' => __('merchant.uploads.empty.description'),
        ])
            @slot('action')
                @include('merchant.pages.partials.empty-upload-action')
            @endslot
        @endcomponent
    @else
        <div x-data="uploadIndex">
            {{-- Mobile card layout --}}
            <div class="space-y-3 md:hidden">
                @foreach ($jobs as $job)
                    @php($showUrl = route('uploads.show', $job))
                    <article class="merchant-card" data-upload-row="{{ $job->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ $showUrl }}" class="merchant-upload-index-link font-semibold text-slate-900 dark:text-slate-100">
                                    #{{ $job->id }}
                                </a>
                                <p class="mt-1 text-sm">
                                    <a href="{{ $showUrl }}" class="merchant-upload-index-link">
                                        {{ $job->type?->label() }}
                                    </a>
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ $job->created_at?->format('M j, Y H:i') }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                @include('merchant.components.upload-status-badge', ['status' => $job->status])
                                <p class="mt-2 text-xs text-slate-500">{{ $job->file_count }} {{ strtolower(__('merchant.uploads.table.files')) }}</p>
                            </div>
                        </div>
                        @include('merchant.pages.uploads.partials.index-actions', ['job' => $job])
                    </article>
                @endforeach
                @if ($jobs->hasPages())
                    <div class="py-2">{{ $jobs->links() }}</div>
                @endif
            </div>

            {{-- Desktop table --}}
            <div class="merchant-card hidden overflow-hidden p-0 md:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">{{ __('merchant.uploads.table.id') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">{{ __('merchant.uploads.table.type') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">{{ __('merchant.uploads.table.status') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">{{ __('merchant.uploads.table.files') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">{{ __('merchant.uploads.table.uploaded_by') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">{{ __('merchant.uploads.table.date') }}</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-200">{{ __('merchant.uploads.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($jobs as $job)
                                @php($showUrl = route('uploads.show', $job))
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/30" data-upload-row="{{ $job->id }}">
                                    <td class="px-4 py-3">
                                        <a href="{{ $showUrl }}" class="merchant-upload-index-link font-medium text-slate-900 dark:text-slate-100">
                                            #{{ $job->id }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ $showUrl }}" class="merchant-upload-index-link">
                                            {{ $job->type?->label() }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        @include('merchant.components.upload-status-badge', ['status' => $job->status])
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $job->file_count }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $job->uploadedBy?->email ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $job->created_at?->format('M j, Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        @include('merchant.pages.uploads.partials.index-actions', ['job' => $job])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($jobs->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-700">{{ $jobs->links() }}</div>
                @endif
            </div>
        </div>

        <script>
            window.__merchantUploadIndex = {
                deleteTitle: @js(__('merchant.uploads.delete.confirm_title')),
                deleteText: @js(__('merchant.uploads.delete.confirm_text')),
                deleteConfirm: @js(__('merchant.uploads.delete.confirm_button')),
                deleteSuccess: @js(__('merchant.uploads.delete.success')),
            };
        </script>
    @endif
@endsection
