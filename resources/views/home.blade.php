@extends('layouts.marketing')

@section('title', config('printflow.brand.name').' — Print-ready PDF workflows')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-b from-amber-50 to-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-amber-600">Built for Shopee sellers</p>
                <h1 class="mt-4 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    Merge, normalize &amp; print — without the API
                </h1>
                <p class="mt-6 text-lg leading-relaxed text-slate-600">
                    Upload thermal labels, order PDFs, and picking lists. {{ config('printflow.brand.name') }} validates, merges, and prepares print-ready outputs locally — fast, secure, and mobile-friendly.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('register') }}" class="w-full rounded-lg bg-amber-600 px-6 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-amber-600/25 hover:bg-amber-500 sm:w-auto">
                        Start free
                    </a>
                    <a href="{{ route('login') }}" class="w-full rounded-lg border border-slate-300 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto">
                        Log in
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-slate-900">Everything you need for fulfillment printing</h2>
                <p class="mt-4 text-slate-600">Phase 1 focuses on reliable local processing — no marketplace API required.</p>
            </div>
            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'PDF merge', 'desc' => 'Combine multiple order PDFs while preserving layout and barcodes.'],
                    ['icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'title' => 'Thermal labels', 'desc' => 'Validate and normalize thermal prints for consistent output.'],
                    ['icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'title' => 'Picking lists', 'desc' => 'Aggregate CSV/XLSX data into grouped picking outputs.'],
                    ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'title' => 'Delivery labels', 'desc' => 'Generate clean address labels with smart spacing.'],
                ] as $feature)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="workflow" class="bg-slate-50 py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900">Simple upload workflow</h2>
                    <p class="mt-4 text-slate-600">Drag files, track progress, download print-ready results — optimized for warehouse teams on mobile and desktop.</p>
                    <ol class="mt-8 space-y-4 text-sm text-slate-700">
                        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">1</span> Select upload type (order PDF, thermal, picking list, or label)</li>
                        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">2</span> Drop PDF, CSV, or XLSX files</li>
                        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">3</span> Processing runs locally with full audit trail</li>
                        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">4</span> Download merged or normalized output</li>
                    </ol>
                </div>
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:mt-0">
                    @foreach (['Order PDF' => 'Merge multi-page orders', 'Thermal' => 'Barcode-safe normalize', 'Picking' => 'CSV/XLSX aggregate', 'Labels' => 'Address PDF output'] as $title => $desc)
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="font-semibold text-slate-900">{{ $title }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $desc }}</p>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full w-2/3 rounded-full bg-amber-500"></div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Preview UI</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="modules" class="py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-slate-900 px-6 py-12 text-center sm:px-12">
                <h2 class="text-2xl font-bold text-white sm:text-3xl">Ready to streamline your print desk?</h2>
                <p class="mx-auto mt-4 max-w-xl text-slate-300">Create a merchant account and start uploading. Admins can manage billing and merchants from the panel.</p>
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('register') }}" class="w-full rounded-lg bg-amber-500 px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-amber-400 sm:w-auto">Create account</a>
                    <a href="{{ url('/'.config('printflow.admin.path')) }}" class="w-full rounded-lg border border-slate-600 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 sm:w-auto">Admin access</a>
                </div>
            </div>
        </div>
    </section>
@endsection
