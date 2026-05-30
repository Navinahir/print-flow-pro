<div

    {{ $attributes->class(['merchant-preview-container']) }}

    data-preview-container

    data-preview-width-mm="{{ $widthMm }}"

    data-preview-height-mm="{{ $heightMm }}"

    aria-label="{{ $ariaLabel }}"

>

    <p class="merchant-preview-container__dimensions sr-only">

        {{ $dimensionsLabel }}

    </p>



    <div class="merchant-preview-container__stage" data-preview-stage>

        <div class="merchant-preview-container__canvas-wrap" data-preview-canvas-wrap>

            <div

                class="merchant-preview-container__canvas"

                data-preview-canvas

                style="--preview-width-mm: {{ $widthMm }}; --preview-height-mm: {{ $heightMm }}; --safe-zone-inset-mm: {{ $safeZoneInsetMm }};"

                x-bind:class="{ 'merchant-preview-container__canvas--aspect-warning': aspectWarningVisible }"

            >

                <div class="merchant-preview-container__surface" data-preview-surface>

                    {{ $slot }}



                    @if ($showSafeZone)

                        <x-merchant.preview.safe-zone

                            :inset-mm="$safeZoneInsetMm"

                            :width-mm="$widthMm"

                            :height-mm="$heightMm"

                        />

                    @endif

                </div>

            </div>

        </div>

    </div>



    <div class="merchant-preview-container__footer">
        <p class="merchant-preview-container__size-hint" aria-hidden="true">
            {{ $dimensionsLabel }}
        </p>
    </div>

</div>

