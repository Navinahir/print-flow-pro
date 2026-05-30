<x-merchant.preview.wrapper>
    <x-slot:toolbar>
        <x-merchant.preview.toolbar :print-enabled="true" :require-selection="true" />
    </x-slot:toolbar>

    <x-merchant.preview.aspect-warning />

    <div x-show="!loading" x-cloak class="merchant-preview-body__content">
        <div x-show="selectedId === null" x-cloak class="merchant-preview-empty-stage">
            @include('merchant.components.preview.partials.empty-state')
        </div>

        <div x-show="selectedId !== null" x-cloak class="merchant-preview-body__active">
            <div class="merchant-preview-body__print-target" data-print-area>
                <x-merchant.preview.container>
                    @include('merchant.printing.components.previews.preview-content')
                </x-merchant.preview.container>
            </div>
        </div>
    </div>
</x-merchant.preview.wrapper>
