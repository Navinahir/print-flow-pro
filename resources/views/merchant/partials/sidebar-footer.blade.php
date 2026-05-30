@auth
    <footer class="merchant-sidebar-footer" aria-label="{{ __('merchant.sidebar.footer_label') }}">
        <p class="merchant-sidebar__section-title merchant-sidebar-footer__title">
            {{ __('merchant.nav.account') }}
        </p>

        <div class="space-y-1">
            <a
                href="{{ route('profile.edit') }}"
                class="merchant-sidebar-link {{ request()->routeIs('profile.edit') ? 'merchant-sidebar-link-active' : '' }}"
                data-sidebar-tooltip="{{ __('merchant.nav.profile') }}"
                :aria-label="@js(__('merchant.nav.profile'))"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="merchant-sidebar-link__label">{{ __('merchant.nav.profile') }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="merchant-sidebar-link merchant-sidebar-footer__button text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-950/30 dark:hover:text-red-300"
                    data-sidebar-tooltip="{{ __('merchant.nav.logout') }}"
                    :aria-label="@js(__('merchant.nav.logout'))"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="merchant-sidebar-link__label">{{ __('merchant.nav.logout') }}</span>
                </button>
            </form>
        </div>
    </footer>
@endauth
