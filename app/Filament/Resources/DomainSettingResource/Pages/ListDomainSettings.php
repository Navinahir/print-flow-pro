<?php

declare(strict_types=1);

namespace App\Filament\Resources\DomainSettingResource\Pages;

use App\Filament\Resources\DomainSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListDomainSettings extends ListRecords
{
    protected static string $resource = DomainSettingResource::class;
}
