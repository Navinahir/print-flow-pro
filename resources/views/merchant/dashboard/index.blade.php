@extends('merchant.layouts.app')

@section('title', __('merchant.dashboard.title'))

@section('breadcrumbs')
    @include('merchant.components.breadcrumb', [
        'items' => [
            ['label' => __('merchant.dashboard.title'), 'active' => true],
        ],
    ])
@endsection

@section('page-header')
    @include('merchant.components.page-header', [
        'title' => __('merchant.dashboard.title'),
        'subtitle' => __('merchant.dashboard.subtitle'),
    ])
@endsection

@section('content')
    @php
        use App\Support\Merchant\PrintingNavigation;
        $printingNavItems = PrintingNavigation::items();
    @endphp

    <div class="space-y-6">
        <div class="merchant-card">
            <p class="text-slate-700 dark:text-slate-300">{{ __('merchant.dashboard.welcome', ['name' => auth()->user()->name]) }}</p>
            @if (auth()->user()->merchant)
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    {{ __('merchant.dashboard.merchant_account', ['name' => auth()->user()->merchant->name]) }}
                </p>
            @endif
        </div>

        @if (\App\Support\MerchantConfig::feature('uploads'))
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="merchant-card text-center">
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $stats->totalUploads }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.dashboard.stats.total_uploads') }}</p>
                </div>
                <div class="merchant-card text-center">
                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $stats->pendingJobs }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.dashboard.stats.pending_jobs') }}</p>
                </div>
                <div class="merchant-card text-center">
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $stats->completedJobs }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.dashboard.stats.completed_jobs') }}</p>
                </div>
            </div>

            @if ($stats->recentJobs->isNotEmpty())
                <div class="merchant-card">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('merchant.dashboard.stats.recent_uploads') }}</h2>
                    <ul class="mt-4 divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach ($stats->recentJobs as $job)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-slate-900 dark:text-slate-100">#{{ $job->id }} · {{ $job->type?->label() }}</p>
                                    <p class="text-xs text-slate-500">{{ $job->created_at?->format('M j, Y H:i') }}</p>
                                </div>
                                <a href="{{ route('uploads.show', $job) }}" class="shrink-0 text-sm font-medium text-amber-700 hover:text-amber-600">
                                    {{ __('merchant.uploads.table.view') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        <div class="grid gap-6 sm:grid-cols-2">
            @can('create', \App\Models\UploadJob::class)
                @if (\App\Support\MerchantConfig::feature('uploads'))
                    <a href="{{ route('uploads.create') }}" class="merchant-card group transition hover:border-amber-300 hover:shadow-md dark:hover:border-amber-600">
                        <h2 class="font-semibold text-slate-900 group-hover:text-amber-700 dark:text-slate-100 dark:group-hover:text-amber-400">
                            {{ __('merchant.dashboard.cards.new_upload.title') }}
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                            {{ __('merchant.dashboard.cards.new_upload.description') }}
                        </p>
                    </a>
                @endif
            @endcan

            @if (\App\Support\MerchantConfig::feature('uploads'))
                <a href="{{ route('uploads.index') }}" class="merchant-card group transition hover:border-amber-300 hover:shadow-md dark:hover:border-amber-600">
                    <h2 class="font-semibold text-slate-900 group-hover:text-amber-700 dark:text-slate-100 dark:group-hover:text-amber-400">
                        {{ __('merchant.dashboard.cards.upload_history.title') }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        {{ __('merchant.dashboard.cards.upload_history.description') }}
                    </p>
                </a>
            @endif
        </div>

        @if (count($printingNavItems) > 0)
            <div class="merchant-card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ __('merchant.printing.section_title') }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            {{ __('merchant.printing.dashboard_description') }}
                        </p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
                        {{ trans_choice('merchant.printing.modules_available', count($printingNavItems), ['count' => count($printingNavItems)]) }}
                    </span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($printingNavItems as $item)
                        @php
                            /** @var \App\Enums\PrintingModule $module */
                            $module = $item['module'];
                        @endphp
                        <a
                            href="{{ route($module->routeName()) }}"
                            class="merchant-printing-module-card group"
                        >
                            <h3 class="font-medium text-slate-900 group-hover:text-amber-700 dark:text-slate-100 dark:group-hover:text-amber-400">
                                {{ $item['label'] }}
                            </h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                {{ __($module->translationKey().'.subtitle') }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="merchant-card border-dashed bg-slate-50 dark:bg-slate-900/50">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('merchant.printing.section_title') }}</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.printing.nav_none_enabled') }}</p>
            </div>
        @endif
    </div>
@endsection
