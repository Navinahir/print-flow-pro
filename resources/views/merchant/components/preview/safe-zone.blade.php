<div
    {{ $attributes->class(['merchant-preview-safe-zone']) }}
    data-preview-safe-zone
    data-safe-zone-inset-mm="{{ $insetMm }}"
    style="--safe-zone-inset-mm: {{ $insetMm }}; --preview-width-mm: {{ $widthMm }}; --preview-height-mm: {{ $heightMm }};"
    x-show="safeZoneVisible"
    x-cloak
    role="presentation"
    aria-hidden="true"
>
    <span class="sr-only">{{ $label }}</span>
</div>
