<?php

declare(strict_types=1);

namespace App\Filament\Resources\UploadJobResource\Pages;

use App\Filament\Resources\UploadJobResource;
use Filament\Resources\Pages\ListRecords;

class ListUploadJobs extends ListRecords
{
    protected static string $resource = UploadJobResource::class;
}
