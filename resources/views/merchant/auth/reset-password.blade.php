@extends('merchant.layouts.guest')

@section('title', __('auth.reset_password.title'))

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">{{ __('auth.reset_password.title') }}</h1>
    <p class="mt-2 text-sm text-slate-600">{{ __('auth.reset_password.subtitle') }}</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-merchant.form.field name="email" :required="true" :label="__('auth.reset_password.email')">
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" autofocus autocomplete="username" placeholder="{{ __('auth.reset_password.email_placeholder') }}" class="merchant-input" />
        </x-merchant.form.field>

        <x-merchant.form.field name="password" :required="true" :label="__('auth.reset_password.password')">
            <input id="password" type="password" name="password" autocomplete="new-password" placeholder="{{ __('auth.reset_password.password_placeholder') }}" class="merchant-input" />
        </x-merchant.form.field>

        <x-merchant.form.field name="password_confirmation" :required="true" :label="__('auth.reset_password.password_confirm')">
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="{{ __('auth.reset_password.password_confirm_placeholder') }}" class="merchant-input" />
        </x-merchant.form.field>

        <button type="submit" class="merchant-btn-primary w-full">
            {{ __('auth.reset_password.submit') }}
        </button>
    </form>
@endsection
