<div
    {{ $attributes->class(['merchant-preview-wrapper']) }}
    data-merchant-preview-root
>
    @if (isset($toolbar))
        {{ $toolbar }}
    @endif

    <div class="merchant-preview-body relative flex flex-1 flex-col">
        {{ $slot }}

        @if ($loadingOverlay)
            <div
                class="merchant-preview-loading-overlay"
                x-show="loading"
                x-cloak
            >
                @include('merchant.components.loading-state', ['overlay' => true])
            </div>
        @endif
    </div>
</div>
