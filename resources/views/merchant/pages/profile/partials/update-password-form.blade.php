<section class="max-w-xl space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('merchant.profile.password.title') }}</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.profile.password.description') }}</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <x-merchant.form.field name="current_password" bag="updatePassword" :required="true" :label="__('merchant.profile.password.current')" label-for="update_password_current_password">
            <input
                id="update_password_current_password"
                type="password"
                name="current_password"
                autocomplete="current-password"
                placeholder="{{ __('merchant.profile.password.current_placeholder') }}"
                class="merchant-input"
            />
        </x-merchant.form.field>

        <x-merchant.form.field name="password" bag="updatePassword" :required="true" :label="__('merchant.profile.password.new')" label-for="update_password_password">
            <input
                id="update_password_password"
                type="password"
                name="password"
                autocomplete="new-password"
                placeholder="{{ __('merchant.profile.password.new_placeholder') }}"
                class="merchant-input"
            />
        </x-merchant.form.field>

        <x-merchant.form.field name="password_confirmation" bag="updatePassword" :required="true" :label="__('merchant.profile.password.confirm')" label-for="update_password_password_confirmation">
            <input
                id="update_password_password_confirmation"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                placeholder="{{ __('merchant.profile.password.confirm_placeholder') }}"
                class="merchant-input"
            />
        </x-merchant.form.field>

        <div class="flex items-center gap-4">
            <button type="submit" class="merchant-btn-primary">{{ __('merchant.profile.password.save') }}</button>
            @if (session('status') === 'password-updated')
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.profile.password.saved') }}</p>
            @endif
        </div>
    </form>
</section>
