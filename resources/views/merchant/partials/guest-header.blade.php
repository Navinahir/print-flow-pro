@php
    $brandName = \App\Support\MerchantConfig::get('brand.name', __('merchant.brand.name'));
@endphp

<header class="merchant-guest-header">
    <div class="merchant-guest-header__inner">
        <a href="{{ route('login') }}" class="merchant-guest-header__brand">
            <x-merchant.brand-mark size="sm" />
            <span class="merchant-guest-header__brand-name">{{ $brandName }}</span>
        </a>

        <div class="merchant-guest-header__controls">
            <x-merchant.locale-switcher />
            <x-merchant.theme-switch />
        </div>
    </div>
</header>
