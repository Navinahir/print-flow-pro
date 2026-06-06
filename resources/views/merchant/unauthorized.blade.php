@extends('merchant.layouts.guest')

@section('title', __('merchant.unauthorized.title'))

@section('content')
    <div class="text-center">
        <p class="inline-flex items-center rounded-full border border-amber-300/60 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200">
            {{ __('merchant.unauthorized.status') }}
        </p>

        <h1 class="mt-6 text-2xl font-bold text-slate-900 dark:text-slate-100">
            {{ __('merchant.unauthorized.heading') }}
        </h1>

        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
            {{ __('merchant.unauthorized.message') }}
        </p>

        
    </div>
@endsection
