<?php

declare(strict_types=1);

namespace App\Support;

use App\Services\Merchant\ThemeService;
use Illuminate\Http\Request;

final class ThemeHelper
{
    public static function htmlClasses(Request $request): string
    {
        /** @var ThemeService $service */
        $service = app(ThemeService::class);

        return $service->isDarkMode($request) ? 'dark' : '';
    }
}
