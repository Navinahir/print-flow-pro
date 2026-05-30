<div class="merchant-printing-list-pane__header">
    <h2 class="merchant-printing-list-pane__heading">
        {{ __('merchant.printing.workspace.list_heading') }}
    </h2>
    <p class="merchant-printing-list-pane__description">
        {{ __('merchant.delivery_labels.csv.list_description') }}
    </p>
</div>

<div class="merchant-printing-list-pane__body">
    <div class="merchant-printing-list-pane__upload">
        <label class="merchant-label text-xs">
            {{ __('merchant.delivery_labels.csv.upload_label') }}
        </label>

        <label class="merchant-btn-secondary mt-2 inline-flex cursor-pointer items-center gap-2 text-xs">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <span>{{ __('merchant.delivery_labels.csv.choose_file') }}</span>
            <input
                type="file"
                accept=".csv,text/csv"
                class="sr-only"
                x-on:change="handleCsvInputChange($event)"
                x-bind:disabled="csvUploading"
            />
        </label>

        <p class="mt-2 text-[10px] text-slate-500 dark:text-slate-400">
            {{ __('merchant.delivery_labels.csv.upload_hint') }}
        </p>

        <div x-show="csvUploading" x-cloak class="mt-3">
            @include('merchant.components.loading-state', [
                'message' => __('merchant.delivery_labels.csv.uploading'),
                'overlay' => false,
            ])
        </div>
    </div>

    <div class="merchant-printing-list-pane__scroll">
        @if (count($listItems) === 0)
            <div class="merchant-printing-list-pane__empty">
                {{ __('merchant.delivery_labels.csv.list_empty') }}
            </div>
        @else
            <ul class="space-y-2" role="listbox" aria-label="{{ __('merchant.printing.workspace.list_heading') }}">
                @foreach ($listItems as $item)
                    <li>
                        @include('merchant.printing.components.list-item-card', ['item' => $item])
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
