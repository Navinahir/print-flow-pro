@extends('merchant.layouts.guest')



@section('title', __('auth.login.title'))



@section('content')

    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ __('auth.login.title') }}</h1>

    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('auth.login.subtitle') }}</p>



    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">

        @csrf



        <x-merchant.form.field name="email" :required="true" :label="__('auth.login.email')">

            <input

                id="email"

                type="email"

                name="email"

                value="{{ old('email') }}"

                autofocus

                autocomplete="username"

                placeholder="{{ __('auth.login.email_placeholder') }}"

                class="merchant-input"

            />

        </x-merchant.form.field>



        <x-merchant.form.field name="password" :required="true" :label="__('auth.login.password')">

            <input

                id="password"

                type="password"

                name="password"

                autocomplete="current-password"

                placeholder="{{ __('auth.login.password_placeholder') }}"

                class="merchant-input"

            />

        </x-merchant.form.field>



        <div class="flex items-center">

            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" name="remember">

            <label for="remember_me" class="ms-2 text-sm text-slate-600 dark:text-slate-400">{{ __('auth.login.remember') }}</label>

        </div>



        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            @if (Route::has('password.request'))

                <a class="text-sm text-amber-700 hover:text-amber-600" href="{{ route('password.request') }}">

                    {{ __('auth.login.forgot_password') }}

                </a>

            @endif

            <button type="submit" class="merchant-btn-primary w-full sm:w-auto">

                {{ __('auth.login.submit') }}

            </button>

        </div>

    </form>



    <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">

        {{ __('auth.login.no_account') }}

        <a href="{{ route('register') }}" class="font-medium text-amber-700 hover:text-amber-600">

            {{ __('auth.login.register_link') }}

        </a>

    </p>

@endsection

