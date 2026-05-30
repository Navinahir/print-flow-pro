<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant\Printing;

use App\Enums\PrintingModule;
use App\Services\Merchant\Printing\LogisticsLabelsService;

class LogisticsLabelsController extends PrintingModuleController
{
    public function __construct(
        private readonly LogisticsLabelsService $logisticsLabelsService,
    ) {}

    protected function module(): PrintingModule
    {
        return PrintingModule::LogisticsLabels;
    }

    protected function service(): LogisticsLabelsService
    {
        return $this->logisticsLabelsService;
    }
}
