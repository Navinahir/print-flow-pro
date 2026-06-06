<?php

declare(strict_types=1);

namespace App\Filament\Resources\DomainSettingResource\Pages;

use App\Filament\Resources\DomainSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditDomainSetting extends EditRecord
{
    protected static string $resource = DomainSettingResource::class;
}
