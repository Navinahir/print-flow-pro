<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\NavigationGroup;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Widgets\PlatformStatsWidget;
use App\Filament\Widgets\RecentAuditLogsWidget;
use App\Http\Middleware\ConfigureDomainSession;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureExpectedSurface;
use App\Http\Middleware\ObfuscateAdminAccess;
use App\Http\Middleware\RejectUnmappedDomain;
use App\Http\Middleware\ResolveRegion;
use App\Support\Domains\DomainContext;
use App\Support\Domains\DomainResolver;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Actions\Action;
use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
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
        $adminDomain = (string) config('domains.admin.domain');
        $adminPath = config('domains.admin.path_prefix', config('printflow.admin.path', 'boss'));
        $resolver = $this->app->make(DomainResolver::class);

        $panel = $panel
            ->default()
            ->id('admin')
            ->path($adminPath);

        if (
            config('domains.routing_enabled', true)
            && filled($adminDomain)
            && ! $resolver->usesPortBasedLocalHost($adminDomain)
        ) {
            $panel = $panel->domain($adminDomain);
        }

        return $panel
            ->login()
            ->profile(EditProfile::class, isSimple: false)
            ->authGuard('web')
            ->brandName(fn (): string => request()->routeIs('filament.admin.auth.login')
                ? (string) config('printflow.admin.login_brand_name', 'XYCubic Admin Portal')
                : (string) config('printflow.admin.brand_name', 'XYCubic Admin'))
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
                ResolveRegion::class,
                ConfigureDomainSession::class,
                RejectUnmappedDomain::class,
                EnsureExpectedSurface::class.':'.DomainContext::SURFACE_ADMIN,
                ObfuscateAdminAccess::class,
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
                EnsureAdminAccess::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): \Illuminate\Contracts\View\View => view('filament.partials.sidebar-account-styles'),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): \Illuminate\Contracts\View\View => view('filament.partials.sidebar-account'),
            )
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action
                    ->label(__('admin.nav.profile'))
                    ->sort(-2),
                'logout' => fn (Action $action): Action => $action
                    ->label(__('admin.nav.logout'))
                    ->sort(-1),
            ]);
    }
}
