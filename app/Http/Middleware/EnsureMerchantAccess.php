<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesUnauthorizedSurfaceAccess;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMerchantAccess
{
    use DeniesUnauthorizedSurfaceAccess;

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null) {
            return $next($request);
        }

        if ($user instanceof User && $user->canAccessMerchantSurface()) {
            return $next($request);
        }

        return $this->denyUnauthorizedAccess(
            $request,
            route('login', absolute: false),
        );
    }
}
