<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Merchant\LocaleService;
use App\Support\Domains\DomainContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetMerchantLocale
{
    public function __construct(
        private readonly LocaleService $localeService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var DomainContext|null $context */
        $context = app(DomainContext::class);

        if ($context?->isMerchant() ?? false) {
            $locale = $this->localeService->current($request);
            app()->setLocale($locale);
            config(['app.locale' => $locale]);
        }

        return $next($request);
    }
}
