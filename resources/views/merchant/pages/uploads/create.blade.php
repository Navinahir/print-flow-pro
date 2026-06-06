@extends('merchant.layouts.app')

@section('title', __('merchant.uploads.create_title'))

@section('breadcrumbs')
    @include('merchant.components.breadcrumb', [
        'items' => [
            ['label' => __('merchant.nav.uploads'), 'url' => route('uploads.index')],
            ['label' => __('merchant.uploads.create_title'), 'active' => true],
        ],
    ])
@endsection

@section('page-header')
    @component('merchant.components.page-header', [
        'title' => __('merchant.uploads.create_title'),
        'subtitle' => __('merchant.uploads.create_subtitle'),
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
        id="merchant-upload-form-root"
        data-initial-type="{{ old('type', '') }}"
        x-data="uploadForm"
        class="merchant-upload-create w-full"
    >
        <div class="merchant-upload-create__layout">
            <div class="merchant-upload-create__main">
                <form method="POST" action="{{ route('uploads.store') }}" enctype="multipart/form-data" class="space-y-6" x-on:submit="submitting = true">
                    @csrf

                    <div class="merchant-card">
                        <x-merchant.form.field name="type" :required="true" :label="__('merchant.uploads.form.type_label')">
                            <select id="type" name="type" x-model="type" class="merchant-input">
                                <option value="">{{ __('merchant.uploads.form.type_placeholder') }}</option>
                                @foreach ($uploadTypes as $uploadType)
                                    <option value="{{ $uploadType->value }}" @selected(old('type') === $uploadType->value)>{{ $uploadType->label() }}</option>
                                @endforeach
                            </select>
                        </x-merchant.form.field>
                        <p class="mt-2 text-xs text-slate-500" x-show="type === 'order_pdf' || type === 'thermal_label'">
                            {{ __('merchant.uploads.form.accepted_pdf') }}
                        </p>
                        <p class="mt-2 text-xs text-slate-500" x-show="type === 'delivery_label'" x-cloak>
                            {{ __('merchant.uploads.form.accepted_delivery') }}
                        </p>
                        <p class="mt-2 text-xs text-slate-500" x-show="type === 'picking_list'" x-cloak>
                            {{ __('merchant.uploads.form.accepted_spreadsheet') }}
                        </p>
                    </div>

                    <div
                        class="merchant-card"
                        x-show="type === 'thermal_label' && fileList.length > 1"
                        x-cloak
                    >
                        <div class="merchant-upload-thermal-output-option">
                            <div class="merchant-upload-thermal-output-option__copy">
                                <p class="merchant-upload-thermal-output-option__label">{{ __('merchant.uploads.form.thermal_output_heading') }}</p>
                                <p class="merchant-upload-thermal-output-option__hint">{{ __('merchant.uploads.form.thermal_output_hint') }}</p>
                            </div>
                            <label class="merchant-upload-toggle merchant-upload-thermal-output-option__toggle">
                                <input type="hidden" name="thermal_combined_output" :value="thermalCombinedOutput ? 1 : 0">
                                <input
                                    type="checkbox"
                                    class="merchant-upload-toggle__input"
                                    x-model="thermalCombinedOutput"
                                >
                                <span class="merchant-upload-toggle__track" aria-hidden="true"></span>
                                <span class="merchant-upload-toggle__text" x-text="thermalCombinedOutput ? @js(__('merchant.uploads.form.thermal_output_combined')) : @js(__('merchant.uploads.form.thermal_output_separate'))"></span>
                            </label>
                        </div>
                    </div>

                    <div
                        class="merchant-dropzone"
                        :class="{ 'merchant-dropzone-active': dragging }"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="handleDrop($event)"
                    >
                        <input type="file" name="files[]" id="merchant-upload-files" class="sr-only" multiple :accept="accept" x-on:change="handleSelect($event)" />
                        <label for="merchant-upload-files" class="cursor-pointer">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="mt-4 text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ __('merchant.uploads.form.dropzone_title') }}
                                <span class="text-amber-700 dark:text-amber-400">{{ __('merchant.uploads.form.dropzone_browse') }}</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ __('merchant.uploads.form.dropzone_limits', [
                                    'count' => \App\Support\MerchantConfig::get('upload.max_files_per_job', 20),
                                    'size' => number_format((int) \App\Support\MerchantConfig::get('upload.max_file_size_kb', 20480) / 1024, 0),
                                ]) }}
                            </p>
                        </label>
                        <ul class="mt-6 space-y-2 text-left" x-show="fileList.length" x-cloak>
                            <template x-for="(file, index) in fileList" :key="index">
                                <li class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm shadow-sm dark:bg-slate-900">
                                    <span class="truncate text-slate-700 dark:text-slate-200" x-text="file.name"></span>
                                    <span class="shrink-0 text-xs text-slate-500 dark:text-slate-400" x-text="formatSize(file.size)"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <x-merchant.form.error name="files" />
                    <x-merchant.form.error name="files.*" />

                    <div x-show="submitting" x-cloak class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
                        {{ __('merchant.uploads.form.uploading') }}
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('uploads.index') }}" class="merchant-btn-secondary">
                            {{ __('merchant.uploads.form.cancel') }}
                        </a>
                        <button type="submit" class="merchant-btn-primary" x-bind:disabled="submitting || !fileList.length">
                            {{ __('merchant.uploads.form.submit') }}
                        </button>
                    </div>
                </form>
            </div>

            <aside class="merchant-upload-create__aside" aria-label="{{ __('merchant.uploads.guides.heading') }}">
                @include('merchant.pages.uploads.partials.type-guide', ['guides' => $uploadGuides])
            </aside>
        </div>

        @include('merchant.pages.uploads.partials.sample-preview-modal')
    </div>
@endsection
