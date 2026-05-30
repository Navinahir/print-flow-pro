<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Domains\DomainContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RootController
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (! config('domains.routing_enabled', true)) {
            return view('home');
        }

        $context = app(DomainContext::class);

        if ($context->isMarketing()) {
            return view('home');
        }

        if ($context->isAdmin()) {
            $prefix = trim((string) config('domains.admin.path_prefix', 'boss'), '/');

            return redirect("/{$prefix}");
        }

        if ($context->isMerchant()) {
            return redirect()->route('dashboard');
        }

        abort(Response::HTTP_NOT_FOUND);
    }
}
