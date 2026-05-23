<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">Upload history</h2>
            @can('create', \App\Models\UploadJob::class)
                <a href="{{ route('uploads.create') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">
                    New upload
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Files</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Uploaded by</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Date</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($jobs as $job)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-900">#{{ $job->id }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $job->type?->label() }}</td>
                                    <td class="px-4 py-3"><x-upload-status-badge :status="$job->status" /></td>
                                    <td class="px-4 py-3 text-slate-600">{{ $job->file_count }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $job->uploadedBy?->email ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $job->created_at?->format('M j, Y H:i') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('uploads.show', $job) }}" class="font-medium text-amber-700 hover:text-amber-600">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                        No uploads yet.
                                        @can('create', \App\Models\UploadJob::class)
                                            <a href="{{ route('uploads.create') }}" class="font-medium text-amber-700">Upload your first files</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($jobs->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3">{{ $jobs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
