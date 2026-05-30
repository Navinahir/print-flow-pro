<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Enums\PrintingModule;
use App\Services\Merchant\Printing\PickingListService;

class PickingListController extends PrintingModuleController
{
    public function __construct(
        private readonly PickingListService $pickingListService,
    ) {}

    protected function module(): PrintingModule
    {
        return PrintingModule::PickingList;
    }

    protected function service(): PickingListService
    {
        return $this->pickingListService;
    }
}
