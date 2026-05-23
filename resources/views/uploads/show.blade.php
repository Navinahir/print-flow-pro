<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">Upload #{{ $job->id }}</h2>
            <a href="{{ route('uploads.index') }}" class="text-sm font-medium text-amber-700 hover:text-amber-600">← Back to history</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status') === 'upload-received')
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Your files were received successfully. Processing will begin shortly.
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500">Type</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ $job->type?->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500">Status</dt>
                        <dd class="mt-1"><x-upload-status-badge :status="$job->status" /></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500">Uploaded by</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $job->uploadedBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500">File count</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $job->file_count }}</dd>
                    </div>
                </dl>
            </div>

            @if ($job->pdfUploads->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-900">PDF files</h3>
                    <ul class="mt-4 divide-y divide-slate-100">
                        @foreach ($job->pdfUploads as $file)
                            <li class="flex items-center justify-between py-3 text-sm">
                                <span class="truncate text-slate-700">{{ $file->original_name }}</span>
                                <span class="shrink-0 text-slate-500">{{ number_format($file->size_bytes / 1024, 1) }} KB</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (! empty($job->metadata['spreadsheet_files']))
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-900">Spreadsheet files</h3>
                    <ul class="mt-4 divide-y divide-slate-100">
                        @foreach ($job->metadata['spreadsheet_files'] as $file)
                            <li class="flex items-center justify-between py-3 text-sm">
                                <span class="truncate text-slate-700">{{ $file['original_name'] }}</span>
                                <span class="shrink-0 text-slate-500">{{ number_format(($file['size_bytes'] ?? 0) / 1024, 1) }} KB</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                PDF preview and download links will appear here after processing is implemented.
            </div>
        </div>
    </div>
</x-app-layout>
