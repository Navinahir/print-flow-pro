@php
    use Illuminate\Support\Facades\Route;

    $currentLocale = app()->getLocale();

    $marketingLocales = collect(config('marketing.locales', []))
        ->map(function (array $definition, string $locale): array {
            $routeName = $definition['route'] ?? null;
            $url = '/tw';

            if (is_string($routeName) && Route::has($routeName)) {
                $url = route($routeName);
            }

            return [
                'locale' => $locale,
                'label' => __('marketing.locales.'.$locale),
                'code' => __('marketing.locale_codes.'.$locale),
                'url' => $url,
            ];
        })
        ->values()
        ->all();
@endphp

<div class="flex items-center gap-1">
    <div class="relative" id="localeSwitcher">
        <button
            type="button"
            id="localeToggle"
            class="marketing-icon-btn"
            aria-haspopup="true"
            aria-expanded="false"
            aria-controls="localeMenu"
            title="{{ __('marketing.locales.'.$currentLocale) }}"
            aria-label="{{ __('marketing.ui.language') }}: {{ __('marketing.locales.'.$currentLocale) }}"
        >
            <span class="material-symbols-outlined text-[22px]" aria-hidden="true">language</span>
            <span id="localeLabel" class="text-label-sm font-label-sm">
                {{ __('marketing.locale_codes.'.$currentLocale) }}
            </span>
        </button>

        <div
            id="localeMenu"
            class="hidden absolute right-0 top-full z-50 mt-2 min-w-[12rem] whitespace-nowrap overflow-hidden rounded-[5px] border border-surface-container bg-surface-container-lowest shadow-lg"
            role="menu"
        >
            @foreach ($marketingLocales as $localeOption)
                <a
                    href="{{ $localeOption['url'] }}"
                    data-marketing-locale="{{ $localeOption['locale'] }}"
                    role="menuitem"
                    @class([
                        'block px-4 py-2 text-label-md font-label-md transition-colors hover:bg-surface-container hover:text-primary',
                        'bg-surface-container-low text-primary' => $currentLocale === $localeOption['locale'],
                        'text-on-surface' => $currentLocale !== $localeOption['locale'],
                    ])
                >
                    {{ $localeOption['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    <button
        type="button"
        id="themeToggle"
        class="marketing-icon-btn"
        aria-label="{{ __('marketing.ui.toggle_color_mode') }}"
        title="{{ __('marketing.ui.toggle_color_mode') }}"
    >
        <span class="material-symbols-outlined text-[22px] dark:hidden" aria-hidden="true">dark_mode</span>
        <span class="material-symbols-outlined hidden text-[22px] dark:inline" aria-hidden="true">light_mode</span>
    </button>
</div>
