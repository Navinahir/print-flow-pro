@props([
    'guides' => [],
])

@php
    /** @var array<string, \App\DTOs\Merchant\Upload\UploadTypeGuideData> $guides */
@endphp

<div class="merchant-upload-guide">
    @foreach ($guides as $typeValue => $guide)
        <div
            class="merchant-upload-guide__panel merchant-card border border-amber-200/60 bg-amber-50/40 dark:border-amber-700/40 dark:bg-amber-950/20"
            x-show="type === @js($typeValue)"
            x-cloak
        >
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                {{ __('merchant.uploads.guides.heading') }}
            </h2>

            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700 dark:text-slate-300">
                @foreach ($guide->instructions as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>

            @if ($guide->rejections !== [])
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-900/50 dark:bg-red-950/30">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-800 dark:text-red-300">
                        {{ __('merchant.uploads.guides.rejections_heading') }}
                    </p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700 dark:text-red-200">
                        @foreach ($guide->rejections as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($guide->samples !== [])
                <div class="mt-4 border-t border-amber-200/60 pt-4 dark:border-amber-800/40">
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                        {{ __('merchant.uploads.guides.samples_heading') }}
                    </p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                        {{ __('merchant.uploads.guides.samples_intro') }}
                    </p>
                    <ul class="mt-3 space-y-2">
                        @foreach ($guide->samples as $sample)
                            <li class="flex items-center gap-3 rounded-lg bg-white/80 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/60">
                                <div class="min-w-0 flex-1">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $sample->label }}</span>
                                    @if ($sample->description)
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $sample->description }}</p>
                                    @endif
                                </div>
                                <div class="merchant-upload-sample-actions shrink-0">
                                    <button
                                        type="button"
                                        class="merchant-upload-sample-icon-btn"
                                        title="{{ __('merchant.uploads.guides.sample_preview') }}"
                                        aria-label="{{ __('merchant.uploads.guides.sample_preview') }}: {{ $sample->label }}"
                                        x-on:click="openSamplePreview(@js($sample->previewUrl()), @js($sample->label), @js($sample->previewKind), @js($sample->downloadName))"
                                    >
                                        @include('merchant.pages.uploads.partials.icons.eye')
                                    </button>
                                    <a
                                        href="{{ asset($sample->assetPath) }}"
                                        download="{{ $sample->downloadName }}"
                                        class="merchant-upload-sample-icon-btn"
                                        title="{{ __('merchant.uploads.guides.sample_download') }}"
                                        aria-label="{{ __('merchant.uploads.guides.sample_download') }}: {{ $sample->label }}"
                                    >
                                        @include('merchant.pages.uploads.partials.icons.download')
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endforeach
</div>
