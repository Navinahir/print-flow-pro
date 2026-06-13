@php
    $brandName = __('merchant.brand.name');
@endphp

<header class="merchant-guest-header">
    <div class="merchant-guest-header__inner">
        <a href="{{ route('login') }}" class="merchant-guest-header__brand">
            <img alt="{{ __('marketing.brand.logo_alt') }}" class="h-10 w-auto" src="{{ asset('images/logo.svg') }}" />
        </a>

        <div class="merchant-guest-header__controls">
            <x-merchant.locale-switcher />
            <x-merchant.theme-switch />
        </div>
    </div>
</header>
