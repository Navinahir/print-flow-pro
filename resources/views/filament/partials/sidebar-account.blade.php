@php
    use Filament\Support\Icons\Heroicon;

    $profileUrl = filament()->getProfileUrl();
    $logoutUrl = filament()->getLogoutUrl();
    $isProfileActive = request()->routeIs('filament.admin.auth.profile');
    $sidebarCollapsible = filament()->isSidebarCollapsibleOnDesktop();
@endphp

@if (filament()->auth()->check() && filled($profileUrl) && filled($logoutUrl))
    <div
        class="fi-admin-sidebar-account"
        aria-label="{{ __('admin.sidebar.footer_label') }}"
    >
        <p
            @if ($sidebarCollapsible)
                x-show="$store.sidebar.isOpen"
                x-transition:enter="fi-transition-enter"
                x-transition:enter-start="fi-transition-enter-start"
                x-transition:enter-end="fi-transition-enter-end"
            @endif
            class="fi-admin-sidebar-account__title"
        >
            {{ __('admin.nav.account') }}
        </p>

        <ul class="fi-sidebar-group-items">
            <x-filament-panels::sidebar.item
                :active="$isProfileActive"
                :icon="Heroicon::OutlinedUserCircle"
                :url="$profileUrl"
            >
                {{ __('admin.nav.profile') }}
            </x-filament-panels::sidebar.item>

            <li class="fi-sidebar-item fi-sidebar-item-has-url">
                <form method="POST" action="{{ $logoutUrl }}" class="fi-admin-sidebar-account__logout-form">
                    @csrf

                    <button
                        type="submit"
                        @if ($sidebarCollapsible)
                            x-data="{ tooltip: false }"
                            x-effect="
                                tooltip = $store.sidebar.isOpen
                                    ? false
                                    : {
                                          content: @js(__('admin.nav.logout')),
                                          placement: document.dir === 'rtl' ? 'left' : 'right',
                                          theme: $store.theme,
                                      }
                            "
                            x-tooltip.html="tooltip"
                        @endif
                        class="fi-sidebar-item-btn fi-admin-sidebar-account__logout-btn"
                    >
                        {{ \Filament\Support\generate_icon_html(Heroicon::OutlinedArrowLeftEndOnRectangle, attributes: (new \Illuminate\View\ComponentAttributeBag)->class(['fi-sidebar-item-icon']), size: \Filament\Support\Enums\IconSize::Large) }}

                        <span
                            @if ($sidebarCollapsible)
                                x-show="$store.sidebar.isOpen"
                                x-transition:enter="fi-transition-enter"
                                x-transition:enter-start="fi-transition-enter-start"
                                x-transition:enter-end="fi-transition-enter-end"
                            @endif
                            class="fi-sidebar-item-label text-red-600"
                        >
                            {{ __('admin.nav.logout') }}
                        </span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
@endif
