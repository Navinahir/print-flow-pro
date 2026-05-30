<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Domains\DomainContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectUnmappedDomain
{
    /**
     * @var list<string>
     */
    private const EXCLUDED_PATHS = [
        'up',
        'health',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('domains.routing_enabled', true)) {
            return $next($request);
        }

        if ($this->isExcludedPath($request)) {
            return $next($request);
        }

        $context = app(DomainContext::class);

        if ($context->isKnown()) {
            return $next($request);
        }

        abort(Response::HTTP_NOT_FOUND);
    }

    private function isExcludedPath(Request $request): bool
    {
        $path = trim($request->path(), '/');

        return in_array($path, self::EXCLUDED_PATHS, true);
    }
}
