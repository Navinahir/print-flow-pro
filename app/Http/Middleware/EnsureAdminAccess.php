<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\DeniesUnauthorizedSurfaceAccess;
use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    use DeniesUnauthorizedSurfaceAccess;

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null) {
            return $next($request);
        }

        if ($user instanceof User && $user->canAccessAdminSurface()) {
            return $next($request);
        }

        $loginUrl = Filament::getCurrentOrDefaultPanel()?->getLoginUrl()
            ?? url('/'.trim((string) config('domains.admin.path_prefix', 'boss'), '/').'/login');

        return $this->denyUnauthorizedAccess($request, $loginUrl);
    }
}
