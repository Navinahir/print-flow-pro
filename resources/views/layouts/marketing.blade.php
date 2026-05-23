<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('printflow.brand.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-white">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur" x-data="{ open: false }">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="text-lg font-bold text-slate-900">
                {{ config('printflow.brand.name') }}
            </a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="#features" class="hover:text-amber-600">Features</a>
                <a href="#workflow" class="hover:text-amber-600">Workflow</a>
                <a href="#modules" class="hover:text-amber-600">Modules</a>
            </nav>
            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ url('/'.config('printflow.admin.path')) }}" class="text-sm font-medium text-slate-600 hover:text-amber-600">Admin</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 hover:text-amber-600">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">Get started</a>
                @endauth
            </div>
            <button type="button" class="md:hidden rounded-lg p-2 text-slate-600" @click="open = !open" aria-label="Toggle menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <div class="border-t border-slate-200 px-4 py-4 md:hidden" x-show="open" x-cloak>
            <div class="flex flex-col gap-3 text-sm font-medium">
                <a href="#features">Features</a>
                <a href="#workflow">Workflow</a>
                <a href="{{ url('/'.config('printflow.admin.path')) }}">Admin</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-amber-600">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    <a href="{{ route('register') }}" class="text-amber-600">Register</a>
                @endauth
            </div>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-lg font-bold text-slate-900">{{ config('printflow.brand.name') }}</p>
                    <p class="mt-1 text-sm text-slate-600">Print-ready PDF workflows for Shopee sellers.</p>
                </div>
                <div class="flex flex-wrap gap-6 text-sm text-slate-600">
                    <a href="{{ route('login') }}" class="hover:text-amber-600">Log in</a>
                    <a href="{{ route('register') }}" class="hover:text-amber-600">Register</a>
                    <a href="{{ url('/'.config('printflow.admin.path')) }}" class="hover:text-amber-600">Admin panel</a>
                </div>
            </div>
            <p class="mt-8 text-center text-xs text-slate-500">&copy; {{ date('Y') }} {{ config('printflow.brand.name') }}. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
