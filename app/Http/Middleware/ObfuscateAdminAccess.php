<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\AdminUnauthorizedResponse;
use App\Support\Domains\DomainContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ObfuscateAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        
        $context = app(DomainContext::class);

        if (! $context->isAdmin()) {
            return $next($request);
        }

        $path = trim($request->path(), '/');
        if ($path === '') {
            return AdminUnauthorizedResponse::make($request);
        }

        if ($this->isAllowedAdminPath($request)) {
            return $next($request);
        }

        abort(Response::HTTP_NOT_FOUND);
    }

    private function isAllowedAdminPath(Request $request): bool
    {
        $prefix = trim((string) config('domains.admin.path_prefix', 'boss'), '/');

        if ($prefix === '') {
            return false;
        }

        $path = trim($request->path(), '/');

        if ($path === $prefix) {
            return true;
        }

        return str_starts_with($path, "{$prefix}/");
    }
}
