<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Domains\DomainContext;
use App\Support\Domains\DomainResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureDomainSession
{
    public function __construct(
        private readonly DomainResolver $domainResolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $context = app()->bound(DomainContext::class)
            ? app(DomainContext::class)
            : $this->domainResolver->resolve($request);

        if (! config('domains.routing_enabled', true)) {
            $this->applyLocalSessionDefaults($request);

            return $next($request);
        }

        if ($context->isKnown()) {
            config([
                'session.cookie' => $this->domainResolver->sessionCookieName($context),
            ]);
            $this->applyLocalSessionDefaults($request);
        }

        return $next($request);
    }

    /**
     * Port-based localhost dev must not use SESSION_DOMAIN=localhost — browsers
     * often drop the cookie and Filament/Livewire POSTs return 419 Page Expired.
     */
    private function applyLocalSessionDefaults(Request $request): void
    {
        if ($this->isLocalDevelopmentHost($request->getHost())) {
            config(['session.domain' => null]);
        }
    }

    private function isLocalDevelopmentHost(string $host): bool
    {
        $host = strtolower($host);

        return $host === 'localhost'
            || $host === '127.0.0.1'
            || str_ends_with($host, '.localhost');
    }
}
