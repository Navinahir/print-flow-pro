@extends('merchant.layouts.guest')

@section('title', __('auth.forgot_password.title'))

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">{{ __('auth.forgot_password.title') }}</h1>
    <p class="mt-2 text-sm text-slate-600">{{ __('auth.forgot_password.subtitle') }}</p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf

        <x-merchant.form.field name="email" :required="true" :label="__('auth.forgot_password.email')">
            <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus placeholder="{{ __('auth.forgot_password.email_placeholder') }}" class="merchant-input" />
        </x-merchant.form.field>

        <button type="submit" class="merchant-btn-primary w-full">
            {{ __('auth.forgot_password.submit') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ route('login') }}" class="font-medium text-amber-700 hover:text-amber-600">
            {{ __('auth.forgot_password.back_to_login') }}
        </a>
    </p>
@endsection
