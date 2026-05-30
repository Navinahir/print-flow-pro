<div class="relative" x-data="merchantThemeSwitch(@js($currentTheme))" x-on:keydown.escape.window="open = false">
    <button
        type="button"
        class="merchant-nav-icon-btn"
        x-on:click="open = ! open"
        :aria-expanded="open"
        aria-haspopup="menu"
        aria-label="{{ __('merchant.theme.switcher_label') }}"
    >
        <span class="sr-only">{{ __('merchant.theme.switcher_label') }}</span>
        <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>

    <div
        class="merchant-nav-dropdown"
        x-show="open"
        x-cloak
        x-transition
        x-on:click.outside="open = false"
        role="menu"
        aria-label="{{ __('merchant.theme.switcher_label') }}"
    >
        <button
            type="button"
            class="merchant-nav-dropdown__item merchant-nav-dropdown__item--selectable"
            role="menuitemradio"
            x-on:click="setTheme('light')"
            :class="{ 'merchant-nav-dropdown__item--active': preference === 'light' }"
            :aria-checked="preference === 'light'"
        >
            <span>{{ __('merchant.theme.light') }}</span>
            <svg class="merchant-nav-dropdown__check" x-show="preference === 'light'" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </button>
        <button
            type="button"
            class="merchant-nav-dropdown__item merchant-nav-dropdown__item--selectable"
            role="menuitemradio"
            x-on:click="setTheme('dark')"
            :class="{ 'merchant-nav-dropdown__item--active': preference === 'dark' }"
            :aria-checked="preference === 'dark'"
        >
            <span>{{ __('merchant.theme.dark') }}</span>
            <svg class="merchant-nav-dropdown__check" x-show="preference === 'dark'" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </button>
        <button
            type="button"
            class="merchant-nav-dropdown__item merchant-nav-dropdown__item--selectable"
            role="menuitemradio"
            x-on:click="setTheme('system')"
            :class="{ 'merchant-nav-dropdown__item--active': preference === 'system' }"
            :aria-checked="preference === 'system'"
        >
            <span>{{ __('merchant.theme.system') }}</span>
            <svg class="merchant-nav-dropdown__check" x-show="preference === 'system'" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </button>
    </div>
</div>
