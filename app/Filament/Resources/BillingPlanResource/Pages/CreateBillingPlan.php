<?php

declare(strict_types=1);

namespace App\Filament\Resources\BillingPlanResource\Pages;

use App\Filament\Resources\BillingPlanResource;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateBillingPlan extends CreateRecord
{
    protected static string $resource = BillingPlanResource::class;

    protected function afterCreate(): void
    {
        app(AuditLogService::class)->logAdmin(
            event: 'billing_plan.created',
            description: "Billing plan {$this->record->name} created.",
            auditable: $this->record,
        );
    }
}
