<?php

declare(strict_types=1);

namespace App\Http\Middleware\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

trait DeniesUnauthorizedSurfaceAccess
{
    protected function denyUnauthorizedAccess(Request $request, string $redirectUrl): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this workspace.');
        }

        return redirect()->to($redirectUrl)
            ->withErrors(['email' => 'You do not have access to this workspace.']);
    }
}
