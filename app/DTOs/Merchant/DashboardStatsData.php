<?php

declare(strict_types=1);

namespace App\DTOs\Merchant;

use App\Models\UploadJob;
use Illuminate\Support\Collection;

final readonly class DashboardStatsData
{
    /**
     * @param  Collection<int, UploadJob>  $recentJobs
     */
    public function __construct(
        public int $totalUploads,
        public int $pendingJobs,
        public int $completedJobs,
        public Collection $recentJobs,
    ) {}
}
