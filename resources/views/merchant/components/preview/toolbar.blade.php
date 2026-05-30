<div {{ $attributes->class(['merchant-preview-toolbar']) }}>
    <div class="merchant-preview-toolbar__info">
        <h2 class="merchant-preview-toolbar__heading">
            {{ $heading }}
        </h2>
        <p class="merchant-preview-toolbar__description">
            {{ $description }}
        </p>
    </div>

    <div class="merchant-preview-toolbar__actions">
        @if ($showSafeZoneToggle)
            @if ($requireSelection)
                <button
                    type="button"
                    class="merchant-btn-secondary merchant-preview-toolbar__safe-zone-btn"
                    x-on:click="toggleSafeZone()"
                    x-bind:disabled="selectedId === null"
                    :aria-pressed="safeZoneVisible"
                    :title.attr="selectedId === null
                        ? @js(__('merchant.preview.toolbar.safe_zone_disabled_hint'))
                        : (safeZoneVisible ? @js(__('merchant.preview.safe_zone.toggle_hide')) : @js(__('merchant.preview.safe_zone.toggle_show')))"
                >
                    <span x-show="safeZoneVisible" x-cloak>{{ __('merchant.preview.safe_zone.toggle_hide') }}</span>
                    <span x-show="! safeZoneVisible" x-cloak>{{ __('merchant.preview.safe_zone.toggle_show') }}</span>
                </button>
            @else
                <button
                    type="button"
                    class="merchant-btn-secondary merchant-preview-toolbar__safe-zone-btn"
                    x-on:click="toggleSafeZone()"
                    :aria-pressed="safeZoneVisible"
                    :title.attr="safeZoneVisible ? @js(__('merchant.preview.safe_zone.toggle_hide')) : @js(__('merchant.preview.safe_zone.toggle_show'))"
                >
                    <span x-show="safeZoneVisible" x-cloak>{{ __('merchant.preview.safe_zone.toggle_hide') }}</span>
                    <span x-show="! safeZoneVisible" x-cloak>{{ __('merchant.preview.safe_zone.toggle_show') }}</span>
                </button>
            @endif
        @endif

        @if (isset($actions))
            {{ $actions }}
        @else
            <x-merchant.preview.print-button :enabled="$printEnabled" :require-selection="$requireSelection" />
        @endif
    </div>
</div>
