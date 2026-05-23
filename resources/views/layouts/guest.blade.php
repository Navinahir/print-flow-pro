<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('printflow.brand.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-50 via-amber-50/30 to-white px-4 py-12">
            <a href="{{ route('home') }}" class="mb-8 text-xl font-bold text-slate-900">
                {{ config('printflow.brand.name') }}
            </a>
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/50">
                {{ $slot }}
            </div>
            <p class="mt-8 text-center text-sm text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-amber-600">← Back to home</a>
            </p>
        </div>
    </body>
</html>
