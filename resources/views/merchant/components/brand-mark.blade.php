@php
    $sizeClass = match ($size) {
        'xs' => 'merchant-brand-mark--xs',
        'sm' => 'merchant-brand-mark--sm',
        'md' => 'merchant-brand-mark--md',
        'lg' => 'merchant-brand-mark--lg',
        default => 'merchant-brand-mark--sm',
    };
@endphp

<span
    {{ $attributes->class(['merchant-brand-mark', $sizeClass]) }}
    @if ($logoUrl) data-has-logo="true" @endif
    @if ($logoUrl) x-data="{ logoFailed: false }" @endif
>
    @if ($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt=""
            class="merchant-brand-mark__image"
            loading="lazy"
            x-show="! logoFailed"
            x-on:error="logoFailed = true"
        />

        <span
            class="merchant-brand-mark__initials"
            x-show="logoFailed"
            x-cloak
            aria-hidden="true"
        >{{ $initials }}</span>
    @else
        <span class="merchant-brand-mark__initials" aria-hidden="true">{{ $initials }}</span>
    @endif
</span>
