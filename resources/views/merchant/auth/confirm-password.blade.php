@extends('merchant.layouts.guest')

@section('title', __('auth.confirm_password.title'))

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">{{ __('auth.confirm_password.title') }}</h1>
    <p class="mt-2 text-sm text-slate-600">{{ __('auth.confirm_password.subtitle') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf

        <x-merchant.form.field name="password" :required="true" :label="__('auth.confirm_password.password')">
            <input id="password" type="password" name="password" autocomplete="current-password" placeholder="{{ __('auth.confirm_password.password_placeholder') }}" class="merchant-input" />
        </x-merchant.form.field>

        <button type="submit" class="merchant-btn-primary w-full">
            {{ __('auth.confirm_password.submit') }}
        </button>
    </form>
@endsection
