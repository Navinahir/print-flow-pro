<div class="sticky top-0 z-50 border-b border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900 lg:hidden">
    <div class="flex h-16 items-center justify-between px-4">
        <button
            type="button"
            class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            x-on:click="toggleSidebar()"
            aria-label="{{ __('merchant.nav.toggle_sidebar') }}"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <a href="{{ route('dashboard') }}" class="text-base font-bold text-slate-900 dark:text-slate-100">
            {{ \App\Support\MerchantConfig::get('brand.name', __('merchant.brand.name')) }}
        </a>

        <button
            type="button"
            class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            x-on:click="toggleMobileNav()"
            aria-label="{{ __('merchant.nav.open_menu') }}"
            :aria-expanded="mobileNavOpen"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </button>
    </div>

    <div
        class="border-t border-slate-200 bg-white px-4 py-4 dark:border-slate-700 dark:bg-slate-900"
        x-show="mobileNavOpen"
        x-transition
        x-cloak
    >
        @auth
            <div class="mb-4 flex items-center justify-end gap-2 border-b border-slate-100 pb-4 dark:border-slate-800">
                @include('merchant.partials.nav-controls')
            </div>
        @endauth

        <nav class="space-y-2 text-sm font-medium" aria-label="{{ __('merchant.nav.open_menu') }}">
            @php
                use App\Support\Merchant\NavigationBuilder;
                $mobileLinks = NavigationBuilder::mobileLinks(request()->route()?->getName());
            @endphp
            @foreach ($mobileLinks as $link)
                <a
                    href="{{ $link['route'] }}"
                    class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-amber-50 dark:text-slate-200 dark:hover:bg-amber-950/30 {{ $link['active'] ? 'bg-amber-50 dark:bg-amber-950/30' : '' }}"
                    x-on:click="closeMobileNav()"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
