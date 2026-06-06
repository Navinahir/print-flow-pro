<header class="sticky top-0 z-30 hidden overflow-visible border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-700 dark:bg-slate-900/90 lg:block">



    <div class="flex h-16 items-center justify-between px-6">



        <div class="flex min-w-0 items-center gap-3">



            <button

                type="button"

                class="merchant-nav-icon-btn hidden shrink-0 lg:inline-flex"

                x-on:click="toggleSidebarCollapse()"

                :aria-expanded="! sidebarCollapsed"

                :aria-label="sidebarCollapsed ? @js(__('merchant.nav.expand_sidebar')) : @js(__('merchant.nav.collapse_sidebar'))"

            >

                <svg class="h-5 w-5 transition-transform" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>

                </svg>

            </button>



            <div class="min-w-0">



                <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ \App\Support\MerchantConfig::get('brand.tagline') ?? __('merchant.brand.tagline') }}</p>



                @auth



                    <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">



                        {{ __('merchant.header.welcome', ['name' => auth()->user()->name]) }}



                    </p>



                @endauth



            </div>



        </div>







        @auth



            @include('merchant.partials.nav-controls')



        @endauth



    </div>



</header>


