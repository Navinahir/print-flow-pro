<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\DTOs\Domain\MerchantDomainConfig;
use App\Services\Domain\DomainConfigurationService;
use App\Support\Domains\DomainContext;
use App\Support\Domains\DomainResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveRegion
{
    public function __construct(
        private readonly DomainResolver $domainResolver,
        private readonly DomainConfigurationService $domainConfiguration,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->domainConfiguration->syncInfrastructureToConfig();

        $context = $this->domainResolver->resolve($request);

        app()->instance(DomainContext::class, $context);
        $this->domainResolver->applyToConfig($context);

        $merchantConfig = $this->domainConfiguration->resolveFromContext($context);
        $this->domainConfiguration->setCurrent($merchantConfig);

        if ($merchantConfig !== null) {
            app()->instance(MerchantDomainConfig::class, $merchantConfig);
        }

        $request->attributes->set('domain_context', $context);
        $request->attributes->set('domain_surface', $context->surface);

        if ($context->regionKey !== null) {
            $request->attributes->set('region_key', $context->regionKey);
            $request->attributes->set('country_code', $context->countryCode);
        }

        return $next($request);
    }
}
