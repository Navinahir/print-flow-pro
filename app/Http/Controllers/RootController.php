<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\AdminUnauthorizedResponse;
use App\Http\Responses\MerchantUnauthorizedResponse;
use App\Support\Domains\DomainContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RootController
{
    public function __invoke(Request $request): View|RedirectResponse|HttpResponse
    {
        if (! config('domains.routing_enabled', true)) {
            return view('home');
        }

        $context = app(DomainContext::class);

        if ($context->isMarketing()) {
            $cookieName = (string) config('marketing.locale_cookie', 'xycubic-marketing-locale');
            $locale = $request->cookie($cookieName);

            if ($locale === 'en') {
                return redirect('/en');
            }

            return redirect('/tw');
        }

        if ($context->isAdmin()) {
            return AdminUnauthorizedResponse::make($request);
        }

        if ($context->isMerchant()) {
            return MerchantUnauthorizedResponse::make($request);
        }

        abort(HttpResponse::HTTP_NOT_FOUND);
    }
}
