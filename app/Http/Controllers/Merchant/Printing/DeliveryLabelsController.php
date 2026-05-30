<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Enums\PrintingModule;
use App\Services\Merchant\Printing\DeliveryLabelsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryLabelsController extends PrintingModuleController
{
    public function __construct(
        private readonly DeliveryLabelsService $deliveryLabelsService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        return view($this->module()->viewName(), [
            'workspace' => $this->deliveryLabelsService->buildDeliveryWorkspace($user),
        ]);
    }

    protected function module(): PrintingModule
    {
        return PrintingModule::DeliveryLabels;
    }

    protected function service(): DeliveryLabelsService
    {
        return $this->deliveryLabelsService;
    }
}
