<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-slate-700">Welcome back, <strong>{{ auth()->user()->name }}</strong>.</p>
                @if (auth()->user()->merchant)
                    <p class="mt-2 text-sm text-slate-600">Merchant: {{ auth()->user()->merchant->name }}</p>
                @endif
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @can('create', \App\Models\UploadJob::class)
                    <a href="{{ route('uploads.create') }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-amber-300 hover:shadow-md">
                        <h3 class="font-semibold text-slate-900 group-hover:text-amber-700">New upload</h3>
                        <p class="mt-2 text-sm text-slate-600">Upload PDFs, CSV, or XLSX for processing.</p>
                    </a>
                @endcan
                <a href="{{ route('uploads.index') }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-amber-300 hover:shadow-md">
                    <h3 class="font-semibold text-slate-900 group-hover:text-amber-700">Upload history</h3>
                    <p class="mt-2 text-sm text-slate-600">Track status and view uploaded files.</p>
                </a>
                @if (auth()->user()->can(\App\Enums\Permission::AccessAdminPanel->value))
                    <a href="{{ url('/'.config('printflow.admin.path')) }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-amber-300 hover:shadow-md">
                        <h3 class="font-semibold text-slate-900 group-hover:text-amber-700">Admin panel</h3>
                        <p class="mt-2 text-sm text-slate-600">Manage merchants, billing, and system logs.</p>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
