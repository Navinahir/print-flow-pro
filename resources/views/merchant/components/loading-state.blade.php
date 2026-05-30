@props([
    'message' => null,
    'overlay' => false,
])

@php
    $loadingMessage = $message ?? __('merchant.components.loading_state.default_message');
@endphp

<div
    {{ $attributes->merge(['class' => $overlay ? 'merchant-loading-overlay' : 'flex flex-col items-center justify-center py-12']) }}
    role="status"
    aria-label="{{ __('merchant.components.loading_state.aria_label') }}"
    aria-live="polite"
>
    <div class="merchant-spinner" aria-hidden="true"></div>
    <p class="mt-4 text-sm font-medium text-slate-600">{{ $loadingMessage }}</p>
</div>
