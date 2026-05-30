<div {{ $attributes->merge(['class' => 'merchant-form-field']) }}>
    @if ($label)
        <x-merchant.form.label :for="$labelFor ?? $name" :required="$required">
            {{ $label }}
        </x-merchant.form.label>
    @endif

    {{ $slot }}

    <x-merchant.form.error :name="$name" :bag="$bag" />
</div>
