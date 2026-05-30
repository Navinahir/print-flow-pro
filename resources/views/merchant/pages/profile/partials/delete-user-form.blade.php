<section class="max-w-xl space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-slate-900">{{ __('merchant.profile.delete.title') }}</h2>
        <p class="mt-1 text-sm text-slate-600">{{ __('merchant.profile.delete.description') }}</p>
    </header>

    <button
        type="button"
        class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
        data-merchant-delete-account
        data-confirm-title="{{ __('merchant.profile.delete.confirm_title') }}"
        data-confirm-text="{{ __('merchant.profile.delete.confirm_text') }}"
        data-password-required="{{ __('merchant.profile.delete.password') }}"
    >
        {{ __('merchant.profile.delete.button') }}
    </button>

    <form id="merchant-delete-account-form" method="POST" action="{{ route('profile.destroy') }}" class="hidden">
        @csrf
        @method('delete')
        <input type="hidden" name="password" value="">
    </form>
</section>
