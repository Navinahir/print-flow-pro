<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\UploadStatus;
use App\Models\Merchant;
use App\Models\UploadJob;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PrintFlowStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Platform overview';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Merchants', (string) Merchant::query()->count())
                ->description('Registered sellers')
                ->icon(Heroicon::OutlinedBuildingStorefront),
            Stat::make('Upload jobs', (string) UploadJob::query()->count())
                ->description('All time')
                ->icon(Heroicon::OutlinedArrowUpTray),
            Stat::make('Processing', (string) UploadJob::query()->where('status', UploadStatus::Processing)->count())
                ->description('Currently running')
                ->icon(Heroicon::OutlinedClock)
                ->color('warning'),
            Stat::make('Failed', (string) UploadJob::query()->where('status', UploadStatus::Failed)->count())
                ->description('Needs attention')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color('danger'),
        ];
    }
}
