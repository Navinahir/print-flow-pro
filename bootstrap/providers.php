<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\PdfServiceProvider;
use App\Providers\RouteServiceProvider;

return [
    AppServiceProvider::class,
    PdfServiceProvider::class,
    RouteServiceProvider::class,
    AdminPanelProvider::class,
];
