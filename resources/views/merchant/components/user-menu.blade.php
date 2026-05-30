@auth
    <div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
        <button
            type="button"
            class="merchant-nav-user-trigger"
            x-on:click="open = ! open"
            :aria-expanded="open"
            aria-haspopup="menu"
            aria-label="{{ __('merchant.user_menu.label') }}"
        >
            <x-merchant.user-avatar :user="auth()->user()" size="sm" />
        </button>

        <div
            class="merchant-nav-user-dropdown"
            x-show="open"
            x-cloak
            x-transition
            x-on:click.outside="open = false"
            role="menu"
            aria-label="{{ __('merchant.user_menu.label') }}"
        >
            <div class="merchant-nav-user-dropdown__header">
                <x-merchant.user-avatar :user="auth()->user()" size="md" />
                <div class="min-w-0 flex-1">
                    <p class="merchant-nav-user-dropdown__name">{{ auth()->user()->name }}</p>
                    @if (auth()->user()->merchant)
                        <p class="merchant-nav-user-dropdown__meta">{{ auth()->user()->merchant->name }}</p>
                    @endif
                </div>
            </div>

            <div class="merchant-nav-user-dropdown__actions">
                <a
                    href="{{ route('profile.edit') }}"
                    class="merchant-nav-user-dropdown__item"
                    role="menuitem"
                    x-on:click="open = false"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ __('merchant.nav.profile') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="merchant-nav-user-dropdown__item merchant-nav-user-dropdown__item--danger" role="menuitem">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        {{ __('merchant.nav.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endauth
