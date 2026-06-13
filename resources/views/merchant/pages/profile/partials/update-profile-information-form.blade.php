<section class="max-w-xl space-y-6">

    <header>

        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('merchant.profile.information.title') }}</h2>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.profile.information.description') }}</p>

    </header>



    <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">

        @csrf

        @method('patch')



        <x-merchant.form.field name="name" :required="true" :label="__('merchant.profile.information.name')">

            <input

                id="name"

                type="text"

                name="name"

                value="{{ old('name', $user->name) }}"

                autocomplete="name"

                placeholder="{{ __('merchant.profile.information.name_placeholder') }}"

                class="merchant-input"

            />

        </x-merchant.form.field>



        <div class="merchant-form-field">

            <x-merchant.form.label for="email" :required="true">

                {{ __('merchant.profile.information.email') }}

            </x-merchant.form.label>

            <input

                id="email"

                type="email"

                name="email"

                value="{{ old('email', $user->email) }}"

                autocomplete="username"

                placeholder="{{ __('merchant.profile.information.email_placeholder') }}"

                class="merchant-input cursor-not-allowed bg-slate-100 text-slate-600 dark:bg-slate-800/60 dark:text-slate-400"

                readonly

            />

            <x-merchant.form.error name="email" />



            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())

                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">

                    {{ __('merchant.profile.information.unverified') }}

                    <button form="send-verification" class="font-medium text-amber-700 hover:text-amber-600 dark:text-amber-400 dark:hover:text-amber-300">

                        {{ __('merchant.profile.information.resend_verification') }}

                    </button>

                </p>

                @if (session('status') === 'verification-link-sent')

                    <p class="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">

                        {{ __('merchant.profile.information.verification_sent') }}

                    </p>

                @endif

            @endif

        </div>



        <div class="flex items-center gap-4">

            <button type="submit" class="merchant-btn-primary">{{ __('merchant.profile.information.save') }}</button>

            @if (session('status') === 'profile-updated')

                <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.profile.information.saved') }}</p>

            @endif

        </div>

    </form>



    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())

        <form id="send-verification" method="POST" action="{{ route('verification.send') }}">

            @csrf

        </form>

    @endif

</section>

