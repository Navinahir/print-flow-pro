@error($name, $bag)
    <p {{ $attributes->merge(['class' => 'merchant-form-error']) }} role="alert">{{ $message }}</p>
@enderror
