<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">New upload</h2>
            <a href="{{ route('uploads.index') }}" class="text-sm font-medium text-amber-700 hover:text-amber-600">← Upload history</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="uploadForm()">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('uploads.store') }}" enctype="multipart/form-data" class="space-y-6" @submit="submitting = true">
                @csrf

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <label for="type" class="block text-sm font-medium text-slate-700">
                        Upload type <span class="text-red-600">*</span>
                    </label>
                    <select id="type" name="type" required x-model="type"
                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Select type…</option>
                        @foreach ($uploadTypes as $uploadType)
                            <option value="{{ $uploadType->value }}" @selected(old('type') === $uploadType->value)>{{ $uploadType->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    <p class="mt-2 text-xs text-slate-500" x-show="type === 'order_pdf' || type === 'thermal_label' || type === 'delivery_label'">Accepted: PDF</p>
                    <p class="mt-2 text-xs text-slate-500" x-show="type === 'picking_list'" x-cloak>Accepted: CSV, XLS, XLSX</p>
                </div>

                <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition"
                    :class="{ 'border-amber-500 bg-amber-50/50': dragging }"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="handleDrop($event)">
                    <input type="file" name="files[]" id="files" class="sr-only" multiple :accept="accept" @change="handleSelect($event)" />
                    <label for="files" class="cursor-pointer">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="mt-4 text-sm font-medium text-slate-700">Drag &amp; drop files here, or <span class="text-amber-700">browse</span></p>
                        <p class="mt-1 text-xs text-slate-500">Max {{ config('printflow.upload.max_files_per_job') }} files · {{ number_format(config('printflow.upload.max_file_size_kb') / 1024, 0) }} MB each</p>
                    </label>
                    <ul class="mt-6 space-y-2 text-left" x-show="fileList.length" x-cloak>
                        <template x-for="(file, index) in fileList" :key="index">
                            <li class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm shadow-sm">
                                <span class="truncate text-slate-700" x-text="file.name"></span>
                                <span class="shrink-0 text-xs text-slate-500" x-text="formatSize(file.size)"></span>
                            </li>
                        </template>
                    </ul>
                </div>
                <x-input-error :messages="$errors->get('files')" class="mt-2" />
                <x-input-error :messages="$errors->get('files.*')" class="mt-2" />

                <div x-show="submitting" x-cloak class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Uploading… please wait.
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('uploads.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50"
                        :disabled="submitting || !fileList.length">
                        Upload files
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function uploadForm() {
            return {
                type: @json(old('type', '')),
                dragging: false,
                submitting: false,
                fileList: [],
                get accept() {
                    if (this.type === 'picking_list') return '.csv,.xlsx,.xls';
                    if (this.type) return '.pdf';
                    return '.pdf,.csv,.xlsx,.xls';
                },
                handleSelect(e) {
                    this.fileList = Array.from(e.target.files);
                },
                handleDrop(e) {
                    this.dragging = false;
                    const input = document.getElementById('files');
                    input.files = e.dataTransfer.files;
                    this.fileList = Array.from(input.files);
                },
                formatSize(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                    return (bytes / 1048576).toFixed(1) + ' MB';
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
