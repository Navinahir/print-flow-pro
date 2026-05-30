<button
    type="button"
    class="merchant-btn-secondary merchant-preview-toolbar__print-btn"
    x-on:click="printPreview()"
    @if ($requireSelection)
        x-bind:disabled="selectedId === null"
        :title.attr="selectedId === null ? @js(__('merchant.preview.toolbar.print_disabled_hint')) : @js(__('merchant.preview.toolbar.print'))"
    @else
        @disabled(! $enabled)
        title="{{ __('merchant.preview.toolbar.print') }}"
    @endif
>
    {{ __('merchant.preview.toolbar.print') }}
</button>
