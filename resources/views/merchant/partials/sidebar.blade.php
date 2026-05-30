@php
    $currentRoute = request()->route()?->getName();
@endphp

<aside
    class="merchant-sidebar fixed inset-y-0 left-0 z-40 hidden flex-col border-r border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900 lg:flex"
    :class="{ '!flex': sidebarOpen }"
    x-cloak
>
    <div class="merchant-sidebar__brand">
        @php
            $brandName = \App\Support\MerchantConfig::get('brand.name', __('merchant.brand.name'));
        @endphp
        <a
            href="{{ route('dashboard') }}"
            class="merchant-sidebar__brand-link"
            data-sidebar-tooltip="{{ $brandName }}"
        >
            <x-merchant.brand-mark size="sm" />
            <span class="merchant-sidebar__brand-text">{{ $brandName }}</span>
        </a>
    </div>

    <nav class="merchant-sidebar__nav" aria-label="{{ __('merchant.nav.dashboard') }}">
        <div>
            <a
                href="{{ route('dashboard') }}"
                class="merchant-sidebar-link {{ $currentRoute === 'dashboard' ? 'merchant-sidebar-link-active' : '' }}"
                data-sidebar-tooltip="{{ __('merchant.nav.dashboard') }}"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="merchant-sidebar-link__label">{{ __('merchant.nav.dashboard') }}</span>
            </a>
        </div>

        <div>
            <p class="merchant-sidebar__section-title">
                {{ __('merchant.nav.operations') }}
            </p>
            <div class="space-y-1">
                @if (\App\Support\MerchantConfig::feature('uploads'))
                    <a
                        href="{{ route('uploads.index') }}"
                        class="merchant-sidebar-link {{ str_starts_with((string) $currentRoute, 'uploads.') ? 'merchant-sidebar-link-active' : '' }}"
                        data-sidebar-tooltip="{{ __('merchant.nav.uploads') }}"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span class="merchant-sidebar-link__label">{{ __('merchant.nav.uploads') }}</span>
                    </a>
                @endif
            </div>
        </div>

        <div>
            <p class="merchant-sidebar__section-title">
                {{ __('merchant.nav.printing') }}
            </p>
            <div class="space-y-1">
                @include('merchant.partials.printing-nav-items')
            </div>
        </div>
    </nav>

    @include('merchant.partials.sidebar-footer')
</aside>

<div
    class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
    x-show="sidebarOpen"
    x-transition.opacity
    x-on:click="sidebarOpen = false"
    x-cloak
></div>
