<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Domains\DomainContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(DomainContext::class);

        if (! $context->isMerchant()) {
            return $next($request);
        }

        if ($context->active) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, __('merchant.errors.region_inactive'));
    }
}
