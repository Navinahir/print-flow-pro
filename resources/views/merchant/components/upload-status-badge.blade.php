@props(['status'])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $classes = match ($value) {
        'completed' => 'bg-emerald-100 text-emerald-800',
        'processing' => 'bg-amber-100 text-amber-800',
        'failed' => 'bg-red-100 text-red-800',
        'cancelled' => 'bg-slate-100 text-slate-700',
        default => 'bg-slate-100 text-slate-700',
    };
    $labelKey = 'merchant.uploads.status.'.$value;
    $label = __($labelKey) !== $labelKey
        ? __($labelKey)
        : ucfirst(str_replace('_', ' ', $value));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $label }}
</span>
