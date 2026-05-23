<x-guest-layout>
    <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
    <p class="mt-2 text-sm text-slate-600">Sign in to manage your uploads and print workflows.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" :required="true" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@shop.example" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" :required="true" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" name="remember">
            <label for="remember_me" class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</label>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm text-amber-700 hover:text-amber-600" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            <x-primary-button class="w-full sm:w-auto justify-center">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        No account?
        <a href="{{ route('register') }}" class="font-medium text-amber-700 hover:text-amber-600">Register</a>
    </p>
</x-guest-layout>
