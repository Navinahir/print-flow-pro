<div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    <button
        type="button"
        class="merchant-nav-icon-btn"
        x-on:click="open = ! open"
        :aria-expanded="open"
        aria-haspopup="listbox"
        aria-label="{{ __('merchant.locale.switcher_label') }}"
    >
        <span class="sr-only">{{ __('merchant.locale.switcher_label') }}</span>
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
        </svg>
        <span class="hidden text-xs font-medium sm:inline">{{ strtoupper(substr($currentLocale, 0, 2)) }}</span>
    </button>

    <div
        class="merchant-nav-dropdown merchant-nav-dropdown--locale"
        x-show="open"
        x-cloak
        x-transition
        x-on:click.outside="open = false"
        role="listbox"
        aria-label="{{ __('merchant.locale.switcher_label') }}"
    >
        @foreach ($locales as $locale)
            <form method="POST" action="{{ route('locale.update') }}" class="w-full" x-on:submit="persistLocalePreference('{{ $locale['code'] }}'); startLocaleSwitch()">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale['code'] }}">
                <button
                    type="submit"
                    class="merchant-nav-dropdown__item merchant-nav-dropdown__item--selectable {{ $locale['code'] === $currentLocale ? 'merchant-nav-dropdown__item--active' : '' }}"
                    role="option"
                    aria-selected="{{ $locale['code'] === $currentLocale ? 'true' : 'false' }}"
                >
                    <span>{{ $locale['label'] }}</span>
                    @if ($locale['code'] === $currentLocale)
                        <svg class="merchant-nav-dropdown__check" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
