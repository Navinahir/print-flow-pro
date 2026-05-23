<?php

declare(strict_types=1);

namespace App\Filament\Resources\MerchantResource\Pages;

use App\Filament\Resources\MerchantResource;
use App\Services\AuditLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMerchant extends EditRecord
{
    protected static string $resource = MerchantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(AuditLogService::class)->logAdmin(
            event: 'merchant.updated',
            description: "Merchant {$this->record->name} updated.",
            auditable: $this->record,
        );
    }
}
