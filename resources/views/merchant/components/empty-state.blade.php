@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'merchant-card py-12 text-center']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        @if (isset($icon))
            {{ $icon }}
        @else
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>
    <h3 class="mt-4 text-lg font-semibold text-slate-900">
        {{ $title ?? __('merchant.components.empty_state.default_title') }}
    </h3>
    <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">
        {{ $description ?? __('merchant.components.empty_state.default_description') }}
    </p>
    @if (isset($action))
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
