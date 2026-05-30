<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Contracts\Merchant\Printing\PrintingModuleServiceInterface;
use App\Enums\PrintingModule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class PrintingModuleController extends Controller
{
    abstract protected function module(): PrintingModule;

    abstract protected function service(): PrintingModuleServiceInterface;

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $workspace = $this->service()->buildWorkspace($user);

        return view($this->module()->viewName(), [
            'workspace' => $workspace,
        ]);
    }
}
