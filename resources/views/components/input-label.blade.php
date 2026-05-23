@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-slate-700']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-red-600" aria-hidden="true">*</span>
        <span class="sr-only">(required)</span>
    @endif
</label>
