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
        <div class="merchant-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('merchant.uploads.table.id') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('merchant.uploads.table.type') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('merchant.uploads.table.status') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('merchant.uploads.table.files') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('merchant.uploads.table.uploaded_by') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('merchant.uploads.table.date') }}</th>
                            <th class="px-4 py-3"><span class="sr-only">{{ __('merchant.uploads.table.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($jobs as $job)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">#{{ $job->id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $job->type?->label() }}</td>
                                <td class="px-4 py-3">
                                    @include('merchant.components.upload-status-badge', ['status' => $job->status])
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $job->file_count }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $job->uploadedBy?->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $job->created_at?->format('M j, Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('uploads.show', $job) }}" class="font-medium text-amber-700 hover:text-amber-600">
                                        {{ __('merchant.uploads.table.view') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($jobs->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $jobs->links() }}</div>
            @endif
        </div>
    @endif
@endsection
