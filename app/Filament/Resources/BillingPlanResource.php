<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\BillingPlanStatus;
use App\Enums\Permission as PermissionEnum;
use App\Filament\Concerns\AuthorizesWithPermission;
use App\Filament\NavigationGroup;
use App\Filament\Resources\BillingPlanResource\Pages\CreateBillingPlan;
use App\Filament\Resources\BillingPlanResource\Pages\EditBillingPlan;
use App\Filament\Resources\BillingPlanResource\Pages\ListBillingPlans;
use App\Filament\Support\FormFields;
use App\Models\BillingPlan;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;

class BillingPlanResource extends Resource
{
    use AuthorizesWithPermission;

    protected static ?string $model = BillingPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MerchantsBilling;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FormFields::text('name', 'Plan name', required: true, placeholder: 'e.g. Pro', maxLength: 255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, callable $set, ?string $operation): void {
                    if ($operation === 'edit') {
                        return;
                    }
                    $set('slug', Str::slug($state ?? ''));
                }),
            FormFields::text('slug', 'Slug', required: true, placeholder: 'pro', maxLength: 255)
                ->unique(ignoreRecord: true),
            FormFields::applyCommon(
                Textarea::make('description'),
                'Description',
            )->rows(3)->placeholder('Short plan summary')->columnSpanFull(),
            FormFields::applyCommon(
                TextInput::make('price_cents'),
                'Price (cents)',
                required: true,
            )->numeric()->minValue(0)->placeholder('2900')->suffix('cents'),
            FormFields::text('currency', 'Currency', required: true, placeholder: 'SGD', maxLength: 3)
                ->default('SGD'),
            FormFields::text('billing_cycle', 'Billing cycle', required: true, placeholder: 'monthly', maxLength: 20)
                ->default('monthly'),
            FormFields::applyCommon(
                Select::make('status'),
                'Status',
                required: true,
            )->options(BillingPlanStatus::class)->native(false),
            FormFields::applyCommon(
                TextInput::make('sort_order'),
                'Sort order',
            )->numeric()->default(0)->placeholder('0'),
            KeyValue::make('features')->label('Features')->columnSpanFull(),
            KeyValue::make('limits')->label('Limits')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state, BillingPlan $record): string => sprintf(
                        '%s %.2f',
                        $record->currency,
                        $state / 100,
                    ))
                    ->sortable(),
                TextColumn::make('billing_cycle')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('merchants_count')
                    ->counts('merchants')
                    ->label('Merchants')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BillingPlanStatus::class),
            ])
            ->defaultSort('sort_order')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBillingPlans::route('/'),
            'create' => CreateBillingPlan::route('/create'),
            'edit' => EditBillingPlan::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return static::authorized(PermissionEnum::ViewBilling->value);
    }

    public static function canCreate(): bool
    {
        return static::authorized(PermissionEnum::ManageBilling->value);
    }

    public static function canEdit(Model $record): bool
    {
        return static::authorized(PermissionEnum::ManageBilling->value);
    }

    public static function canDelete(Model $record): bool
    {
        return static::authorized(PermissionEnum::ManageBilling->value);
    }
}
