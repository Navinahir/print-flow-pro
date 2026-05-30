<div class="merchant-printing-list-pane__header">
    <h2 class="merchant-printing-list-pane__heading">
        {{ __('merchant.printing.workspace.list_heading') }}
    </h2>
    <p class="merchant-printing-list-pane__description">
        {{ __('merchant.printing.workspace.list_description') }}
    </p>
</div>

<div class="merchant-printing-list-pane__body">
    <div class="merchant-printing-list-pane__scroll">
        @if (count($listItems) === 0)
            <div class="merchant-printing-list-pane__empty">
                {{ __('merchant.printing.workspace.list_empty') }}
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
