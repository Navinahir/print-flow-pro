@extends('merchant.layouts.guest')



@section('title', __('auth.register.title'))



@section('content')

    <h1 class="text-2xl font-bold text-slate-900">{{ __('auth.register.title') }}</h1>

    <p class="mt-2 text-sm text-slate-600">{{ __('auth.register.subtitle') }}</p>



    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">

        @csrf



        <x-merchant.form.field name="name" :required="true" :label="__('auth.register.name')">

            <input id="name" type="text" name="name" value="{{ old('name') }}" autofocus autocomplete="name" placeholder="{{ __('auth.register.name_placeholder') }}" class="merchant-input" />

        </x-merchant.form.field>



        <x-merchant.form.field name="email" :required="true" :label="__('auth.register.email')">

            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="{{ __('auth.register.email_placeholder') }}" class="merchant-input" />

        </x-merchant.form.field>



        <x-merchant.form.field name="password" :required="true" :label="__('auth.register.password')">

            <input id="password" type="password" name="password" autocomplete="new-password" placeholder="{{ __('auth.register.password_placeholder') }}" class="merchant-input" />

        </x-merchant.form.field>



        <x-merchant.form.field name="password_confirmation" :required="true" :label="__('auth.register.password_confirm')">

            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="{{ __('auth.register.password_confirm_placeholder') }}" class="merchant-input" />

        </x-merchant.form.field>



        <button type="submit" class="merchant-btn-primary w-full">

            {{ __('auth.register.submit') }}

        </button>

    </form>



    <p class="mt-6 text-center text-sm text-slate-600">

        {{ __('auth.register.has_account') }}

        <a href="{{ route('login') }}" class="font-medium text-amber-700 hover:text-amber-600">

            {{ __('auth.register.login_link') }}

        </a>

    </p>

@endsection

