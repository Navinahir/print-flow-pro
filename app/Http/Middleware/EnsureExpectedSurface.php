<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Domains\DomainContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExpectedSurface
{
    public function handle(Request $request, Closure $next, string $surface): Response
    {
        if (! config('domains.routing_enabled', true)) {
            return $next($request);
        }

        $context = app(DomainContext::class);

        if ($context->surface !== $surface) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $next($request);
    }
}
