<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\MerchantStatus;
use App\Enums\Permission as PermissionEnum;
use App\Filament\Concerns\AuthorizesWithPermission;
use App\Filament\NavigationGroup;
use App\Filament\Resources\MerchantResource\Pages\CreateMerchant;
use App\Filament\Resources\MerchantResource\Pages\EditMerchant;
use App\Filament\Resources\MerchantResource\Pages\ListMerchants;
use App\Filament\Support\FormFields;
use App\Models\Merchant;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class MerchantResource extends Resource
{
    use AuthorizesWithPermission;

    protected static ?string $model = Merchant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MerchantsBilling;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FormFields::text('name', 'Merchant name', required: true, placeholder: 'e.g. Acme Shopee Store', maxLength: 255),
            FormFields::text('shop_name', 'Shop name', placeholder: 'Display name on marketplace', maxLength: 255),
            FormFields::text('email', 'Contact email', placeholder: 'merchant@example.com', maxLength: 255)
                ->email(),
            FormFields::text('phone', 'Phone', placeholder: '+65 9123 4567', maxLength: 30)
                ->tel(),
            FormFields::applyCommon(
                Select::make('status'),
                'Status',
                required: true,
            )->options(MerchantStatus::class)->native(false),
            FormFields::applyCommon(
                Select::make('billing_plan_id'),
                'Billing plan',
            )->relationship('billingPlan', 'name')->searchable()->preload()->nullable()->native(false),
            FormFields::applyCommon(
                Select::make('user_id'),
                'Linked user account',
            )->relationship('user', 'email')->searchable()->preload()->nullable()->native(false),
            FormFields::applyCommon(
                DateTimePicker::make('onboarded_at'),
                'Onboarded at',
            ),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shop_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('billingPlan.name')
                    ->label('Plan')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(MerchantStatus::class),
                SelectFilter::make('billing_plan_id')
                    ->relationship('billingPlan', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Billing plan'),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchants::route('/'),
            'create' => CreateMerchant::route('/create'),
            'edit' => EditMerchant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['billingPlan', 'user']);
    }

    public static function canViewAny(): bool
    {
        return static::authorized(PermissionEnum::ViewMerchants->value);
    }

    public static function canCreate(): bool
    {
        return static::authorized(PermissionEnum::ManageMerchants->value);
    }

    public static function canEdit(Model $record): bool
    {
        return static::authorized(PermissionEnum::ManageMerchants->value);
    }

    public static function canDelete(Model $record): bool
    {
        return static::authorized(PermissionEnum::ManageMerchants->value);
    }
}
