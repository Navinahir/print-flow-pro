@can('create', \App\Models\UploadJob::class)
    <a href="{{ route('uploads.create') }}" class="merchant-btn-primary">
        {{ __('merchant.uploads.empty.action') }}
    </a>
@endcan
