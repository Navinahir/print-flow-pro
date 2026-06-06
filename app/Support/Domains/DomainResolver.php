<?php

declare(strict_types=1);

namespace App\Support\Domains;

use App\Services\Domain\DomainConfigurationService;
use Illuminate\Http\Request;

class DomainResolver
{
    public function __construct(
        private readonly DomainConfigurationService $domainConfiguration,
    ) {}

    public function resolve(Request $request): DomainContext
    {
        $host = $this->normalizeHost(
            $request->getHost(),
            $request->getPort(),
            $request->getScheme(),
        );

        $marketingDomain = $this->normalizeConfiguredHost($this->domainConfiguration->effectiveMarketingHost());
        if ($marketingDomain !== '' && $host === $marketingDomain) {
            return $this->marketingContext($host);
        }

        $adminDomain = $this->normalizeConfiguredHost($this->domainConfiguration->effectiveAdminHost());
        if ($adminDomain !== '' && $host === $adminDomain) {
            return $this->adminContext($host);
        }

        foreach ($this->domainConfiguration->merchantRegionsForRouting() as $regionKey => $region) {
            $merchantDomain = $this->normalizeConfiguredHost((string) ($region['domain'] ?? ''));
            if ($merchantDomain !== '' && $host === $merchantDomain) {
                return $this->merchantContext($host, (string) $regionKey, $region);
            }
        }

        return new DomainContext(
            surface: DomainContext::SURFACE_UNKNOWN,
            regionKey: null,
            countryCode: null,
            locale: (string) config('app.locale', 'en'),
            host: $host,
            domain: null,
            active: false,
        );
    }

    public function routeDomainForSurface(string $surface, ?string $regionKey = null): ?string
    {
        if (! config('domains.routing_enabled', true)) {
            return null;
        }

        return match ($surface) {
            DomainContext::SURFACE_MARKETING => $this->domainForRouting($this->domainConfiguration->effectiveMarketingHost()),
            DomainContext::SURFACE_ADMIN => $this->domainForRouting($this->domainConfiguration->effectiveAdminHost()),
            DomainContext::SURFACE_MERCHANT => $this->merchantRouteDomain($regionKey),
            default => null,
        };
    }

    /**
     * @return list<array{domain: ?string, region_key: string}>
     */
    public function merchantRouteDefinitions(): array
    {
        $definitions = [];

        foreach ($this->domainConfiguration->merchantRegionsForRouting() as $regionKey => $region) {
            $definitions[] = [
                'domain' => $this->routeDomainForSurface(DomainContext::SURFACE_MERCHANT, (string) $regionKey),
                'region_key' => (string) $regionKey,
            ];
        }

        return $definitions;
    }

    public function applyToConfig(DomainContext $context): void
    {
        config([
            'domains.current' => $context->toArray(),
            'app.locale' => $context->locale,
        ]);

        if ($context->countryCode !== null) {
            config(['app.region_country_code' => $context->countryCode]);
        }

        if ($context->regionKey !== null) {
            config(['app.region_key' => $context->regionKey]);
        }
    }

    public function sessionCookieName(DomainContext $context): string
    {
        if ($context->isMarketing()) {
            return (string) config('domains.marketing.session_cookie');
        }

        if ($context->isAdmin()) {
            return (string) config('domains.admin.session_cookie');
        }

        if ($context->isMerchant() && $context->regionKey !== null) {
            $merchantConfig = $this->domainConfiguration->configForRegionKey($context->regionKey);
            if ($merchantConfig?->sessionCookie) {
                return $merchantConfig->sessionCookie;
            }

            $region = $this->domainConfiguration->merchantRegionsForRouting()[$context->regionKey] ?? [];

            return (string) ($region['session_cookie'] ?? config('session.cookie'));
        }

        return (string) config('session.cookie');
    }

    private function merchantRouteDomain(?string $regionKey): ?string
    {
        if ($regionKey === null) {
            return null;
        }

        $region = $this->domainConfiguration->merchantRegionsForRouting()[$regionKey] ?? null;

        if (! is_array($region)) {
            return null;
        }

        return $this->domainForRouting((string) ($region['domain'] ?? ''));
    }

    private function domainForRouting(string $domain): ?string
    {
        $domain = trim($domain);

        if ($domain === '') {
            return null;
        }

        if ($this->usesPortBasedLocalHost($domain)) {
            return null;
        }

        return $this->normalizeConfiguredHost($domain);
    }

    public function usesPortBasedLocalHost(string $domain): bool
    {
        if (! config('domains.port_routing', false)) {
            return false;
        }

        $domain = strtolower(trim($domain));

        if (! str_contains($domain, ':')) {
            return false;
        }

        $hostPart = explode(':', $domain, 2)[0];

        return in_array($hostPart, ['localhost', '127.0.0.1'], true);
    }

    private function marketingContext(string $host): DomainContext
    {
        return new DomainContext(
            surface: DomainContext::SURFACE_MARKETING,
            regionKey: null,
            countryCode: null,
            locale: (string) config('app.locale', 'en'),
            host: $host,
            domain: $this->domainConfiguration->effectiveMarketingHost(),
            active: true,
        );
    }

    private function adminContext(string $host): DomainContext
    {
        return new DomainContext(
            surface: DomainContext::SURFACE_ADMIN,
            regionKey: null,
            countryCode: null,
            locale: (string) config('app.locale', 'en'),
            host: $host,
            domain: $this->domainConfiguration->effectiveAdminHost(),
            active: true,
        );
    }

    /**
     * @param  array<string, mixed>  $region
     */
    private function merchantContext(string $host, string $regionKey, array $region): DomainContext
    {
        return new DomainContext(
            surface: DomainContext::SURFACE_MERCHANT,
            regionKey: $regionKey,
            countryCode: (string) ($region['country_code'] ?? ''),
            locale: (string) ($region['locale'] ?? config('app.locale', 'en')),
            host: $host,
            domain: (string) ($region['domain'] ?? ''),
            active: (bool) ($region['active'] ?? false),
        );
    }

    private function normalizeHost(string $host, ?int $port, ?string $scheme = null): string
    {
        $host = strtolower(trim($host));
        $host = $this->aliasLoopbackHost($host);
        $host = $this->stripStandardPortSuffix($host);

        if ($port !== null && ! $this->hostIncludesPort($host)) {
            $defaultPort = $this->defaultPortForScheme($scheme);
            if ($port !== $defaultPort) {
                return "{$host}:{$port}";
            }
        }

        return $host;
    }

    private function normalizeConfiguredHost(string $domain): string
    {
        $domain = $this->aliasLoopbackHost(strtolower(trim($domain)));

        return $this->stripStandardPortSuffix($domain);
    }

    private function stripStandardPortSuffix(string $host): string
    {
        if (! str_contains($host, ':') || str_starts_with($host, '[')) {
            return $host;
        }

        [$name, $port] = explode(':', $host, 2);

        if (in_array((int) $port, [80, 443], true)) {
            return $name;
        }

        return $host;
    }

    private function aliasLoopbackHost(string $host): string
    {
        if ($host === '127.0.0.1' || str_starts_with($host, '127.0.0.1:')) {
            return 'localhost'.substr($host, strlen('127.0.0.1'));
        }

        return $host;
    }

    private function hostIncludesPort(string $host): bool
    {
        return str_contains($host, ':');
    }

    private function defaultPortForScheme(?string $scheme): int
    {
        return strtolower((string) $scheme) === 'https' ? 443 : 80;
    }
}
