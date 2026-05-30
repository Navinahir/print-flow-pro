<label
    @if ($for !== '') for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'merchant-label']) }}
>
    {{ $slot }}
    @if ($required)
        <span class="merchant-form-required" aria-hidden="true">*</span>
        <span class="sr-only">{{ __('merchant.form.required_indicator') }}</span>
    @endif
</label>
