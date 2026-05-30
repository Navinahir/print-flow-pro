<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ \App\Support\ThemeHelper::htmlClasses(request()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('merchant.dashboard.title')) — {{ \App\Support\MerchantConfig::get('brand.name', __('merchant.brand.name')) }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @hasSection('vite')
        @yield('vite')
    @else
        @vite(['resources/css/merchant.css', 'resources/js/merchant.js'])
    @endif
    <script>
        (function () {
            try {
                document.documentElement.classList.add('merchant-page-loading-pending');
                var theme = localStorage.getItem('merchant_theme') ?? 'system';
                var isDark = theme === 'dark'
                    || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.dataset.merchantTheme = theme;

                if (localStorage.getItem('merchant_sidebar_collapsed') === '1') {
                    document.documentElement.classList.add('merchant-sidebar-collapsed-pending');
                }
                if (localStorage.getItem('merchant_locale_switching') === '1') {
                    document.documentElement.classList.add('merchant-locale-switching-pending');
                }
            } catch (e) {}
        })();
    </script>
</head>
@php
    $previewConfiguration = app(\App\Services\Merchant\Preview\PreviewConfigurationService::class)->configuration();
    $themePreference = app(\App\Services\Merchant\ThemeService::class)->preference(request());
@endphp
<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-800 dark:bg-slate-950 dark:text-slate-100">
    <x-merchant.page-loader />

    @php
        $flashMessages = [
            'upload-received' => __('merchant.flash.upload_received'),
            'profile-updated' => __('merchant.flash.profile_updated'),
            'locale-updated' => __('merchant.flash.locale_updated'),
            'theme-updated' => __('merchant.flash.theme_updated'),
        ];
        $sessionStatus = session('status');
    @endphp

    <div
        id="merchant-app-root"
        x-data="merchantShell"
        class="merchant-layout"
        :class="{ 'merchant-layout--sidebar-collapsed': sidebarCollapsed }"
        data-swal-confirm="{{ __('merchant.sweetalert.confirm') }}"
        data-swal-cancel="{{ __('merchant.sweetalert.cancel') }}"
        data-swal-ok="{{ __('merchant.sweetalert.ok') }}"
        data-ajax-error="{{ __('merchant.ajax.error_default') }}"
        data-ajax-network-error="{{ __('merchant.ajax.network_error') }}"
        data-theme-url="{{ route('theme.update') }}"
        data-theme-preference="{{ $themePreference }}"
        data-current-locale="{{ app()->getLocale() }}"
        data-locale-url="{{ route('locale.update') }}"
        data-preview-config='@json($previewConfiguration->toArray())'
    >
        @include('merchant.partials.mobile-nav')
        @include('merchant.partials.sidebar')

        {{-- Flex column fills viewport height; main flex-1 pushes footer to the bottom on short pages. --}}
        <div class="merchant-layout__body">
            @include('merchant.partials.header')

            <main class="merchant-layout__main">
                @hasSection('breadcrumbs')
                    @yield('breadcrumbs')
                @endif

                @hasSection('page-header')
                    @yield('page-header')
                @endif

                @yield('content')
            </main>

            @include('merchant.partials.footer')
        </div>
    </div>

    <div
        id="merchant-flash-data"
        class="hidden"
        @if ($sessionStatus && isset($flashMessages[$sessionStatus]))
            data-success="{{ $flashMessages[$sessionStatus] }}"
        @endif
        @if (session('error'))
            data-error="{{ session('error') }}"
        @endif
        @if ($errors->any())
            data-error="{{ $errors->first() }}"
        @endif
    ></div>
</body>
</html>
