@php
    /** @var \App\Models\UploadJob $job */
    $canDelete = auth()->user()?->can('delete', $job);
@endphp

<div class="merchant-upload-index-actions mt-3 md:mt-0 md:justify-end">
    <a
        href="{{ route('uploads.show', $job) }}"
        class="merchant-upload-index-actions__btn"
        title="{{ __('merchant.uploads.table.view') }}"
        aria-label="{{ __('merchant.uploads.table.view') }}"
    >
        @include('merchant.pages.uploads.partials.icons.eye')
    </a>
    @if ($canDelete)
        <button
            type="button"
            class="merchant-upload-index-actions__btn merchant-upload-index-actions__btn--danger"
            title="{{ __('merchant.uploads.table.delete') }}"
            aria-label="{{ __('merchant.uploads.table.delete') }}"
            x-on:click="deleteUpload(@js(route('uploads.destroy', $job)), @js((string) $job->id))"
            :disabled="deletingId === @js(route('uploads.destroy', $job))"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25v.916m16.5 0H3.75"/>
            </svg>
        </button>
    @endif
</div>
