<?php

use App\Http\Middleware\ConfigureDomainSession;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureExpectedSurface;
use App\Http\Middleware\EnsureMerchantAccess;
use App\Http\Middleware\EnsureRegionIsActive;
use App\Http\Middleware\ObfuscateAdminAccess;
use App\Http\Middleware\RejectUnmappedDomain;
use App\Http\Middleware\ResolveRegion;
use App\Http\Middleware\SetMerchantLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Must run before StartSession inside the "web" group (session cookie name / domain).
        $middleware->prependToGroup('web', [
            ResolveRegion::class,
            ConfigureDomainSession::class,
        ]);

        $middleware->appendToGroup('web', [
            SetMerchantLocale::class,
        ]);

        $middleware->alias([
            'domain.resolve' => ResolveRegion::class,
            'domain.session' => ConfigureDomainSession::class,
            'domain.reject-unmapped' => RejectUnmappedDomain::class,
            'region.active' => EnsureRegionIsActive::class,
            'admin.obfuscate' => ObfuscateAdminAccess::class,
            'domain.surface' => EnsureExpectedSurface::class,
            'access.admin' => EnsureAdminAccess::class,
            'access.merchant' => EnsureMerchantAccess::class,
            'printing.module' => \App\Http\Middleware\EnsurePrintingModuleEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
