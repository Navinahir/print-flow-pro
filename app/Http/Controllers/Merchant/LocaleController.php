<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\UpdateLocaleRequest;
use App\Services\Merchant\LocaleService;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __construct(
        private readonly LocaleService $localeService,
    ) {}

    public function update(UpdateLocaleRequest $request): RedirectResponse
    {
        $this->localeService->apply($request, $request->validated('locale'));

        return back()
            ->with('status', 'locale-updated')
            ->withCookie($this->localeService->makePreferenceCookie($request->validated('locale')));
    }
}
