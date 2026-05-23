<?php

declare(strict_types=1);

namespace App\Filament\Resources\BillingPlanResource\Pages;

use App\Filament\Resources\BillingPlanResource;
use App\Services\AuditLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBillingPlan extends EditRecord
{
    protected static string $resource = BillingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(AuditLogService::class)->logAdmin(
            event: 'billing_plan.updated',
            description: "Billing plan {$this->record->name} updated.",
            auditable: $this->record,
        );
    }
}
