<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\NavigationGroup;
use App\Filament\Widgets\PlatformStatsWidget;
use App\Filament\Widgets\RecentAuditLogsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $brandName = config('printflow.brand.name', 'XY Cubic Shopee');

        return $panel
            ->default()
            ->id('admin')
            ->path(config('printflow.admin.path', 'bosslogin'))
            ->login()
            ->authGuard('web')
            ->brandName($brandName)
            ->brandLogo(config('printflow.brand.logo'))
            ->favicon(config('printflow.brand.favicon'))
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
            ])
            ->navigationGroups([
                FilamentNavigationGroup::make(NavigationGroup::Overview->value),
                FilamentNavigationGroup::make(NavigationGroup::MerchantsBilling->value),
                FilamentNavigationGroup::make(NavigationGroup::Operations->value),
                FilamentNavigationGroup::make(NavigationGroup::System->value),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                PlatformStatsWidget::class,
                RecentAuditLogsWidget::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop();
    }
}
