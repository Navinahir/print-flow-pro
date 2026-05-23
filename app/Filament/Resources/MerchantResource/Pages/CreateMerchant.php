<?php

declare(strict_types=1);

namespace App\Filament\Resources\MerchantResource\Pages;

use App\Filament\Resources\MerchantResource;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateMerchant extends CreateRecord
{
    protected static string $resource = MerchantResource::class;

    protected function afterCreate(): void
    {
        app(AuditLogService::class)->logAdmin(
            event: 'merchant.created',
            description: "Merchant {$this->record->name} created.",
            auditable: $this->record,
        );
    }
}
