<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentAuditLogsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AuditLog::query()
                    ->with(['user', 'merchant'])
                    ->latest()
                    ->limit(10),
            )
            ->heading('Recent activity')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('channel')
                    ->badge(),
                TextColumn::make('event'),
                TextColumn::make('description')
                    ->limit(40),
                TextColumn::make('user.email')
                    ->label('User'),
            ])
            ->paginated(false);
    }
}
