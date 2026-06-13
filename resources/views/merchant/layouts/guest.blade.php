<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ \App\Support\ThemeHelper::htmlClasses(request()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('auth.login.title')) — {{ __('merchant.brand.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/merchant.css', 'resources/js/merchant-auth.js'])

    <script>

        (function () {

            try {

                document.documentElement.classList.add('merchant-page-loading-pending');

                var theme = localStorage.getItem('merchant_theme') ?? 'system';

                var isDark = theme === 'dark'

                    || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                document.documentElement.classList.toggle('dark', isDark);

                document.documentElement.dataset.merchantTheme = theme;

            } catch (e) {}

        })();

    </script>

</head>

@php

    $themePreference = app(\App\Services\Merchant\ThemeService::class)->preference(request());

@endphp

<body class="font-sans antialiased text-slate-800 dark:text-slate-100">

    <x-merchant.page-loader />



    <div

        id="merchant-app-root"

        x-data="merchantShell"

        class="merchant-guest-layout"

        data-current-locale="{{ app()->getLocale() }}"

        data-locale-url="{{ route('locale.update') }}"

        data-theme-url="{{ route('theme.update') }}"

        data-theme-preference="{{ $themePreference }}"

        data-swal-confirm="{{ __('merchant.sweetalert.confirm') }}"

        data-swal-cancel="{{ __('merchant.sweetalert.cancel') }}"

        data-swal-ok="{{ __('merchant.sweetalert.ok') }}"

        data-ajax-error="{{ __('merchant.ajax.error_default') }}"

        data-ajax-network-error="{{ __('merchant.ajax.network_error') }}"

    >

        @include('merchant.partials.guest-header')



        <main class="merchant-guest-main">

            <div class="merchant-guest-card">

                @yield('content')

            </div>

        </main>



        <footer class="merchant-guest-footer">

            {{ __('merchant.footer.copyright', ['year' => date('Y'), 'brand' => \App\Support\MerchantConfig::get('brand.name', __('merchant.brand.name'))]) }}

        </footer>

    </div>



    <div id="merchant-flash-data" class="hidden" @if ($errors->any()) data-error="{{ $errors->first() }}" @endif></div>

</body>

</html>

