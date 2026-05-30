<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin domain routes
|--------------------------------------------------------------------------
|
| Filament registers the management panel under /{ADMIN_PATH_PREFIX} via
| AdminPanelProvider. This file only defines explicit fallbacks; access
| control is enforced by ObfuscateAdminAccess middleware.
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Filament is auto-registered by AdminPanelProvider
// All admin routes fall back to 404 if not handled by Filament
Route::fallback(function () {
    abort(404, 'Admin route not found');
});
