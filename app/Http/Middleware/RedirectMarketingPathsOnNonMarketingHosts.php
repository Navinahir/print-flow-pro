<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Domains\DomainContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marketing locale URLs (/tw, /en) must not be served on merchant or admin hosts.
 */
class RedirectMarketingPathsOnNonMarketingHosts
{
    /**
     * @var list<string>
     */
    private const MARKETING_LOCALE_PATHS = ['tw', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('domains.routing_enabled', true)) {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        if (! in_array($path, self::MARKETING_LOCALE_PATHS, true)) {
            return $next($request);
        }

        $context = app(DomainContext::class);

        if ($context->isMarketing()) {
            return $next($request);
        }

        if ($context->isMerchant()) {
            return redirect()->route('login');
        }

        if ($context->isAdmin()) {
            $prefix = trim((string) config('domains.admin.path_prefix', 'boss'), '/');

            return redirect("/{$prefix}");
        }

        return $next($request);
    }
}
