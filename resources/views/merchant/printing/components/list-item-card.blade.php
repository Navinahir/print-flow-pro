@props([
    'item',
])

@php
    /** @var \App\DTOs\Merchant\Printing\PrintingListItemData $item */
@endphp

<button
    type="button"
    class="merchant-printing-list-item w-full text-left"
    x-on:click="selectItem(@js($item->id))"
    :class="{ 'merchant-printing-list-item-active': selectedId === @js($item->id) }"
    role="option"
    :aria-selected="selectedId === @js($item->id)"
>
    <span class="flex items-start justify-between gap-2">
        <span class="block font-medium text-slate-900 dark:text-slate-100">{{ $item->title }}</span>
        <span @class([
            'merchant-printing-list-item__status',
            'merchant-printing-list-item__status--' . $item->status,
        ])>
            {{ __('merchant.printing.workspace.status_'.$item->status) }}
        </span>
    </span>
    <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $item->subtitle }}</span>
</button>
