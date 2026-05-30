<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\UpdateThemeRequest;
use App\Services\Merchant\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ThemeController extends Controller
{
    public function __construct(
        private readonly ThemeService $themeService,
    ) {}

    public function update(UpdateThemeRequest $request): JsonResponse|RedirectResponse
    {
        $preference = $request->validated('theme');

        if ($request->expectsJson()) {
            return response()->json([
                'theme' => $preference,
                'message' => __('merchant.theme.updated'),
            ])->withCookie($this->themeService->makePreferenceCookie($preference));
        }

        return back()
            ->with('status', 'theme-updated')
            ->withCookie($this->themeService->makePreferenceCookie($preference));
    }
}
