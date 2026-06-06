<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\DTOs\Merchant\DashboardStatsData;
use App\Enums\UploadStatus;
use App\Models\UploadJob;
use App\Models\User;

class DashboardService
{
    public function statsFor(User $user): DashboardStatsData
    {
        $merchant = $user->merchant;

        if ($merchant === null) {
            return new DashboardStatsData(
                totalUploads: 0,
                pendingJobs: 0,
                completedJobs: 0,
                recentJobs: collect(),
            );
        }

        $baseQuery = UploadJob::query()->where('merchant_id', $merchant->id);

        return new DashboardStatsData(
            totalUploads: (clone $baseQuery)->count(),
            pendingJobs: (clone $baseQuery)->where('status', UploadStatus::Pending)->count(),
            completedJobs: (clone $baseQuery)->where('status', UploadStatus::Completed)->count(),
            recentJobs: (clone $baseQuery)->with('uploadedBy')->latest()->limit(5)->get(),
        );
    }
}
