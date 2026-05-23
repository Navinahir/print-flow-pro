<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Permission as PermissionEnum;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Filament\Concerns\AuthorizesWithPermission;
use App\Filament\NavigationGroup;
use App\Filament\Resources\UploadJobResource\Pages\ListUploadJobs;
use App\Filament\Resources\UploadJobResource\Pages\ViewUploadJob;
use App\Models\UploadJob;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UploadJobResource extends Resource
{
    use AuthorizesWithPermission;

    protected static ?string $model = UploadJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Operations;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('merchant_id')
                ->relationship('merchant', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->native(false),
            Select::make('type')
                ->options(UploadJobType::class)
                ->required()
                ->native(false),
            Select::make('status')
                ->options(UploadStatus::class)
                ->required()
                ->native(false),
            TextInput::make('file_count')
                ->numeric()
                ->minValue(0),
            Textarea::make('error_message')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('merchant.name'),
            TextEntry::make('uploadedBy.email')
                ->label('Uploaded by'),
            TextEntry::make('type'),
            TextEntry::make('status')
                ->badge(),
            TextEntry::make('file_count'),
            TextEntry::make('error_message')
                ->columnSpanFull(),
            TextEntry::make('started_at')
                ->dateTime(),
            TextEntry::make('completed_at')
                ->dateTime(),
            TextEntry::make('created_at')
                ->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('merchant.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('file_count')
                    ->sortable(),
                TextColumn::make('uploadedBy.email')
                    ->label('Uploaded by')
                    ->toggleable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(UploadStatus::class),
                SelectFilter::make('type')
                    ->options(UploadJobType::class),
                SelectFilter::make('merchant_id')
                    ->relationship('merchant', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Merchant'),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUploadJobs::route('/'),
            'view' => ViewUploadJob::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['merchant', 'uploadedBy']);
    }

    public static function canViewAny(): bool
    {
        return static::authorized(PermissionEnum::ViewMerchants->value);
    }

    public static function canView(Model $record): bool
    {
        return static::authorized(PermissionEnum::ViewMerchants->value);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
