<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Controllers\RootController;
use App\Services\Domain\DomainConfigurationService;
use App\Support\Domains\DomainContext;
use App\Support\Domains\DomainResolver;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RouteServiceProvider extends ServiceProvider
{
    public const MARKETING_MIDDLEWARE = [
        'web',
        'domain.surface:'.DomainContext::SURFACE_MARKETING,
        'domain.reject-unmapped',
    ];

    public const MERCHANT_MIDDLEWARE = [
        'web',
        'domain.surface:'.DomainContext::SURFACE_MERCHANT,
        'domain.reject-unmapped',
        'region.active',
        'access.merchant',
    ];

    public const ADMIN_MIDDLEWARE = [
        'web',
        'domain.surface:'.DomainContext::SURFACE_ADMIN,
        'domain.reject-unmapped',
        'admin.obfuscate',
    ];

    public function boot(): void
    {
        $this->routes(function (): void {
            $resolver = $this->app->make(DomainResolver::class);

            $this->registerRootRoute();
            $this->registerMarketingRoutes($resolver);
            $this->registerMerchantRoutes($resolver);
            $this->registerAdminRoutes($resolver);
        });
    }

    private function registerRootRoute(): void
    {
        Route::middleware([
            'web',
            'domain.reject-unmapped',
        ])
            ->get('/', RootController::class)
            ->name('home');
    }

    private function registerMarketingRoutes(DomainResolver $resolver): void
    {
        $domain = $resolver->routeDomainForSurface(DomainContext::SURFACE_MARKETING);

        $this->registerDomainGroup(
            domain: $domain,
            middleware: self::MARKETING_MIDDLEWARE,
            routes: base_path('routes/marketing.php'),
        );
    }

    private function registerMerchantRoutes(DomainResolver $resolver): void
    {
        $definitions = $resolver->merchantRouteDefinitions();
        $routingEnabled = (bool) config('domains.routing_enabled', true);
        $primaryRegionKey = $this->primaryMerchantRegionKey();

        if (! $routingEnabled) {
            Route::middleware(self::MERCHANT_MIDDLEWARE)
                ->group(base_path('routes/merchant.php'));

            return;
        }

        if ($this->isPortBasedLocalRouting($resolver)) {
            Route::middleware(self::MERCHANT_MIDDLEWARE)
                ->group(base_path('routes/merchant.php'));

            return;
        }

        foreach ($definitions as $definition) {
            $domain = $definition['domain'];

            if ($domain === null) {
                continue;
            }

            $regionKey = $definition['region_key'];
            $regions = $this->app->make(DomainConfigurationService::class)->merchantRegionsForRouting();
            $region = $regions[$regionKey] ?? [];
            $isActive = (bool) ($region['active'] ?? false);
            $isPrimary = $regionKey === $primaryRegionKey;

            Route::domain($domain)
                ->middleware(self::MERCHANT_MIDDLEWARE)
                ->group(function () use ($isPrimary, $isActive): void {
                    if ($isPrimary && $isActive) {
                        require base_path('routes/merchant.php');

                        return;
                    }

                    Route::any('/{path?}', function () {
                        abort(Response::HTTP_FORBIDDEN, __('merchant.errors.region_inactive'));
                    })->where('path', '.*');
                });
        }
    }

    private function registerAdminRoutes(DomainResolver $resolver): void
    {
        $domain = $resolver->routeDomainForSurface(DomainContext::SURFACE_ADMIN);

        if ($domain === null) {
            if (! config('domains.routing_enabled', true)) {
                return;
            }

            Route::middleware(self::ADMIN_MIDDLEWARE)
                ->group(base_path('routes/admin.php'));

            return;
        }

        $this->registerDomainGroup(
            domain: $domain,
            middleware: self::ADMIN_MIDDLEWARE,
            routes: base_path('routes/admin.php'),
        );
    }

    /**
     * @param  list<string>  $middleware
     */
    private function registerDomainGroup(
        ?string $domain,
        array $middleware,
        string $routes,
    ): void {
        $group = Route::middleware($middleware);

        if ($domain !== null) {
            $group = $group->domain($domain);
        }

        $group->group($routes);
    }

    private function primaryMerchantRegionKey(): string
    {
        return $this->app->make(DomainConfigurationService::class)->primaryActiveRegionKey();
    }

    private function isPortBasedLocalRouting(DomainResolver $resolver): bool
    {
        return $resolver->usesPortBasedLocalHost((string) config('domains.marketing.domain'));
    }
}
