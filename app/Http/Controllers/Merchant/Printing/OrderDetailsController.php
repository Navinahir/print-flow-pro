<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Enums\PrintingModule;
use App\Services\Merchant\Printing\OrderDetailsService;

class OrderDetailsController extends PrintingModuleController
{
    public function __construct(
        private readonly OrderDetailsService $orderDetailsService,
    ) {}

    protected function module(): PrintingModule
    {
        return PrintingModule::OrderDetails;
    }

    protected function service(): OrderDetailsService
    {
        return $this->orderDetailsService;
    }
}
