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
                <template x-if="selectedPreview()">
                    <div
                        class="delivery-label-preview merchant-preview-surface__inset"
                        data-delivery-label-preview
                        x-init="$nextTick(() => refreshDeliveryLabelLayout($el))"
                    >
                        <div class="delivery-label-preview__header">
                            <p
                                class="delivery-label-preview__recipient"
                                x-text="selectedPreview()?.recipient_name"
                            ></p>
                            <div
                                class="delivery-label-preview__shipping"
                                x-show="Boolean(selectedPreview()?.tracking_number || selectedPreview()?.carrier)"
                            >
                                <p x-show="Boolean(selectedPreview()?.carrier)" x-text="selectedPreview()?.carrier"></p>
                                <p x-show="Boolean(selectedPreview()?.tracking_number)" x-text="selectedPreview()?.tracking_number"></p>
                            </div>
                            <p
                                class="delivery-label-preview__shrink-hint"
                                x-show="selectedPreview()?.is_shrunk"
                                x-text="labels.shrunkHint"
                            ></p>
                        </div>

                        <div
                            class="delivery-label-preview__address-block"
                            data-delivery-label-address
                        >
                            <template x-for="(line, index) in selectedPreview()?.address_lines ?? []" :key="index">
                                <p
                                    class="delivery-label-preview__address-line"
                                    data-delivery-label-address-line
                                    x-text="line"
                                    x-bind:style="{ fontSize: addressFontSizePx() + 'px' }"
                                ></p>
                            </template>
                        </div>

                        <div class="delivery-label-preview__spacer" aria-hidden="true"></div>

                        <div
                            class="delivery-label-preview__remarks"
                            data-delivery-label-remarks
                            x-show="Boolean(selectedPreview()?.remarks)"
                        >
                            <p class="delivery-label-preview__remarks-heading" x-text="labels.remarksHeading"></p>
                            <p
                                class="delivery-label-preview__remarks-body"
                                x-text="selectedPreview()?.remarks"
                            ></p>
                        </div>
                    </div>
                </template>
            </x-merchant.preview.container>
            </div>
        </div>
    </div>
</x-merchant.preview.wrapper>
