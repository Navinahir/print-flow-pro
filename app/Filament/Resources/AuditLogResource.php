<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AuditLogChannel;
use App\Enums\Permission as PermissionEnum;
use App\Filament\Concerns\AuthorizesWithPermission;
use App\Filament\NavigationGroup;
use App\Filament\Resources\AuditLogResource\Pages\ListAuditLogs;
use App\Filament\Resources\AuditLogResource\Pages\ViewAuditLog;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AuditLogResource extends Resource
{
    use AuthorizesWithPermission;

    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::System;

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'audit log';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('channel')
                ->badge(),
            TextEntry::make('event'),
            TextEntry::make('description')
                ->columnSpanFull(),
            TextEntry::make('user.email')
                ->label('User'),
            TextEntry::make('merchant.name')
                ->label('Merchant'),
            TextEntry::make('auditable_type')
                ->label('Subject type'),
            TextEntry::make('auditable_id')
                ->label('Subject ID'),
            TextEntry::make('ip_address'),
            TextEntry::make('user_agent')
                ->columnSpanFull(),
            KeyValueEntry::make('properties')
                ->columnSpanFull(),
            TextEntry::make('created_at')
                ->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('channel')
                    ->badge()
                    ->sortable(),
                TextColumn::make('event')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('merchant.name')
                    ->label('Merchant')
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->options(AuditLogChannel::class),
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
            'index' => ListAuditLogs::route('/'),
            'view' => ViewAuditLog::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'merchant']);
    }

    public static function canViewAny(): bool
    {
        return static::authorized(PermissionEnum::ViewAuditLogs->value);
    }

    public static function canView(Model $record): bool
    {
        return static::authorized(PermissionEnum::ViewAuditLogs->value);
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
