<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\PrintingModule;
use App\Support\MerchantConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrintingModuleEnabled
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $module = PrintingModule::from($moduleKey);

        if (! MerchantConfig::feature($module->featureKey())) {
            abort(Response::HTTP_FORBIDDEN, __('merchant.printing.errors.module_disabled'));
        }

        return $next($request);
    }
}
