<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-theme-storage-key="{{ config('marketing.theme_storage_key') }}"
    data-locale-storage-key="{{ config('marketing.locale_storage_key') }}"
    data-locale-cookie-name="{{ config('marketing.locale_cookie') }}"
    data-default-locale="{{ config('marketing.default_locale') }}"
    data-default-theme="{{ config('marketing.default_theme') }}"
>
<head>
    @php($marketingSurface = app(\App\Support\Domains\DomainContext::class)->isMarketing())
    <script>
        (function () {
            var isMarketingSurface = @json($marketingSurface);

            var themeKey = @json(config('marketing.theme_storage_key'));
            var localeKey = @json(config('marketing.locale_cookie'));
            var localeCookie = @json(config('marketing.locale_cookie'));
            var defaultLocale = @json(config('marketing.default_locale'));
            var defaultTheme = @json(config('marketing.default_theme'));

            try {
                if (! isMarketingSurface) {
                    return;
                }

                var theme = localStorage.getItem(themeKey);
                if (!theme) {
                    theme = defaultTheme;
                    localStorage.setItem(themeKey, theme);
                }
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                var path = window.location.pathname;
                var onEn = path === '/en' || path.indexOf('/en/') === 0;
                var onTw = path === '/tw' || path.indexOf('/tw/') === 0;
                var storedLocale = localStorage.getItem(localeKey);
                var suffix = window.location.search + window.location.hash;

                if (isMarketingSurface && storedLocale === null) {
                    localStorage.setItem(localeKey, defaultLocale);
                    document.cookie = localeCookie + '=' + encodeURIComponent(defaultLocale)
                        + ';path=/;max-age=31536000;SameSite=Lax';

                    if (path === '/' || path === '') {
                        window.location.replace('/tw' + suffix);
                        return;
                    }
                }

                var locale = storedLocale || defaultLocale;
                document.cookie = localeCookie + '=' + encodeURIComponent(locale)
                    + ';path=/;max-age=31536000;SameSite=Lax';

                if (locale === 'en' && !onEn) {
                    window.location.replace('/en' + suffix);
                    return;
                }

                if (locale === 'zh-TW' && !onTw) {
                    window.location.replace('/tw' + suffix);
                }
            } catch (e) {}
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('printflow.brand.name'))</title> 
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&amp;display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
      rel="stylesheet"
    />
    @vite(['resources/css/marketing.css', 'resources/js/marketing.js'])
</head>
<body class="bg-background text-on-background font-body-md overflow-x-hidden none">
    <header
      class="sticky top-0 w-full z-50 bg-surface-container-lowest border-b border-surface-container"
    >
      <nav
        class="flex justify-between items-center w-full px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto h-16"
      >
        <div class="flex items-center gap-8">
          <a class="flex items-center gap-2" href="{{ $marketingHomeUrl }}">
            <img alt="{{ __('marketing.brand.logo_alt') }}" class="h-10 w-auto" src="{{ asset('images/logo.svg') }}" />
          </a>
        </div>

        <div class="flex items-center gap-2 min-[992px]:gap-4">
        <!-- Menu -->
        <div
          id="navMenu"
          class="fixed top-0 right-0 h-[100%] w-80 max-w-[90vw] bg-white z-50 transition-transform duration-300 translate-x-full min-[992px]:static min-[992px]:translate-x-0 min-[992px]:h-auto min-[992px]:w-auto min-[992px]:bg-transparent"
        >
          <div
            class="flex flex-col min-[992px]:flex-row items-start min-[992px]:items-center gap-6 min-[992px]:gap-8 p-6 min-[992px]:p-0 h-full"
          >
            <button id="closeMenu" type="button" class="self-end min-[992px]:hidden" aria-label="{{ __('marketing.ui.close_menu') }}">
              <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>

            <a class="nav-link" href="#features">{{ __('marketing.nav.features') }}</a>
            <a class="nav-link" href="#faq">{{ __('marketing.nav.pricing') }}</a>
            <a class="nav-link" href="#pricing">{{ __('marketing.nav.tutorials') }}</a>
            <a class="nav-link" href="#pricing">{{ __('marketing.nav.resources') }}</a>
            <a class="nav-link" href="#pricing">{{ __('marketing.nav.about_us') }}</a>

            @include('marketing.components.nav-controls')

            <div
              class="flex flex-col min-[992px]:flex-row gap-4 min-[992px]:ml-6 w-full min-[992px]:w-auto mt-4 min-[992px]:mt-0"
            >
                <a
                    href="{{ $merchantRegisterUrl }}"
                    class="bg-primary text-on-primary px-6 py-2 rounded-[5px] font-bold text-center"
                >
                    {{ __('marketing.nav.try_it_now') }}
                </a>
            </div>
          </div>
        </div>

        <div
          id="overlay"
          class="fixed inset-0 z-40 bg-black/50 opacity-0 invisible transition-opacity duration-300 min-[992px]:hidden"
          aria-hidden="true"
        ></div>

        <!-- Hamburger -->
        <button id="menuToggle" type="button" class="min-[992px]:hidden" aria-label="{{ __('marketing.ui.open_menu') }}" aria-expanded="false" aria-controls="navMenu">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-7 h-7"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
        </button>
        </div>
      </nav>
    </header>

    <main class="marketing-main">@yield('content')</main>

    <footer class="marketing-footer w-full border-t border-surface-container">
      <div
        class="w-full px-margin-mobile md:px-margin-desktop pt-8 pb-8 md:pt-12 md:pb-12 max-w-container-max mx-auto flex flex-col md:flex-row justify-between gap-gutter"
      >
        <div class="flex flex-col gap-4 w-[100%] lg:w-[20%] md:w-[40%]">
          <div class="flex items-center gap-2">
            <img alt="{{ __('marketing.brand.logo_alt') }}" class="h-10 w-auto" src="{{ asset('images/logo.svg') }}" />
          </div>
          <p class="marketing-footer__text text-[14px]">
            {{ __('marketing.footer.description') }}
          </p>
          <p class="marketing-footer__text text-label-sm font-label-sm">
            {{ __('marketing.footer.copyright', ['year' => date('Y')]) }}
          </p>
        </div>
        <div class="flex flex-wrap justify-center gap-0 w-[100%] lg:w-[80%] md:w-[60%] pl-0 md:pl-10">
          <div class="w-[100%] lg:w-[25%] sm:w-[50%] px-2 mb-3 lg:mb-0">
            <h4 class="marketing-footer__heading text-[20px] font-bold mb-3">
              {{ __('marketing.footer.features') }}
            </h4>
            <ul>
              <li>
                <a href="#features" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.batch_print_orders') }}
                </a>
              </li>
              <li>
                <a href="#features" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.batch_shipping_labels') }}
                </a>
              </li>
              <li>
                <a href="#features" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.smart_picking_lists') }}
                </a>
              </li>
              <li>
                <a href="#features" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.order_integration') }}
                </a>
              </li>
            </ul>
          </div>
          <div class="w-[100%] lg:w-[25%] sm:w-[50%] px-2 mb-3 lg:mb-0">
            <h4 class="marketing-footer__heading text-[20px] font-bold mb-3">
              {{ __('marketing.footer.resources') }}
            </h4>
            <ul>
              <li>
                <a href="#" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.tutorials') }}
                </a>
              </li>
              <li>
                <a href="#faq" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.faq') }}
                </a>
              </li>
            </ul>
          </div>
          <div class="w-[100%] lg:w-[25%] sm:w-[50%] px-2 mb-3 lg:mb-0">
            <h4 class="marketing-footer__heading text-[20px] font-bold mb-3">
              {{ __('marketing.footer.about_us') }}
            </h4>
            <ul>
              <li>
                <a href="#" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.company_profile') }}
                </a>
              </li>
              <li>
                <a href="#" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.contact') }}
                </a>
              </li>
              <li>
                <a href="{{ route('marketing.privacy') }}" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.privacy_policy') }}
                </a>
              </li>
              <li>
                <a href="{{ route('marketing.terms') }}" class="marketing-footer__link text-[15px] mb-2 inline-block transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.links.terms_of_service') }}
                </a>
              </li>
            </ul>
          </div>
          <div class="w-[100%] lg:w-[25%] sm:w-[50%] px-2 mb-0 lg:mb-0">
            <h4 class="marketing-footer__heading text-[20px] font-bold mb-3">
              {{ __('marketing.footer.contact_us') }}
            </h4>
            <ul>
              <li class="flex gap-2 mb-2">
                <span class="material-symbols-outlined text-primary" aria-hidden="true">mail</span>
                <a href="mailto:service@xycubic.com" class="marketing-footer__link text-[15px] transition-all duration-400 ease-in-out">
                  service@xycubic.com
                </a>
              </li>
              <li class="flex gap-2 mb-2">
                <span class="material-symbols-outlined text-primary" aria-hidden="true">phone_in_talk</span>
                <a href="tel:+886222997129" class="marketing-footer__link text-[15px] transition-all duration-400 ease-in-out">
                  +886-2-2299-7129
                </a>
              </li>
              <li class="flex gap-2 mb-2">
                <span class="material-symbols-outlined text-primary" aria-hidden="true">location_on</span>
                <a href="#" class="marketing-footer__link text-[15px] transition-all duration-400 ease-in-out">
                  {{ __('marketing.footer.address') }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
</body>
</html>
