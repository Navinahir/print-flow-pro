<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Permission as PermissionEnum;
use App\Filament\Concerns\AuthorizesWithPermission;
use App\Filament\NavigationGroup;
use App\Filament\Resources\DomainSettingResource\Pages\EditDomainSetting;
use App\Filament\Resources\DomainSettingResource\Pages\ListDomainSettings;
use App\Filament\Support\FormFields;
use App\Models\DomainSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DomainSettingResource extends Resource
{
    use AuthorizesWithPermission;

    protected static ?string $model = DomainSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::System;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'region_key';

    protected static ?string $navigationLabel = 'Domain settings';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FormFields::text('region_key', 'Region key', required: true, maxLength: 50)
                ->disabled(),
            FormFields::text('host', 'Host', required: true, maxLength: 255)
                ->placeholder('tw.xycubic.com'),
            FormFields::text('country_code', 'Country code', required: true, maxLength: 2),
            FormFields::text('surface', 'Surface', required: true, maxLength: 20)
                ->disabled(),
            Toggle::make('is_active')->label('Active'),
            FormFields::text('session_cookie', 'Session cookie', maxLength: 100),
            FormFields::text('brand_name', 'Brand name', maxLength: 255),
            FormFields::applyCommon(
                TextInput::make('brand_tagline'),
                'Brand tagline',
            )->maxLength(255),
            KeyValue::make('settings')->label('Settings JSON')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('region_key')->searchable()->sortable(),
                TextColumn::make('host')->searchable()->sortable(),
                TextColumn::make('surface')->badge(),
                TextColumn::make('country_code'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDomainSettings::route('/'),
            'edit' => EditDomainSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return static::authorized(PermissionEnum::ManageMerchants->value);
    }

    public static function canEdit(Model $record): bool
    {
        return static::authorized(PermissionEnum::ManageMerchants->value);
    }
}
