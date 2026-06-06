<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Services\Merchant\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function __invoke(): View
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);

        return view('merchant.dashboard.index', [
            'stats' => $this->dashboardService->statsFor($user),
        ]);
    }
}
