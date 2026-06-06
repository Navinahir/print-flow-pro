<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing;

use App\DTOs\Merchant\Printing\PrintingListItemData;
use App\Enums\UploadJobType;
use App\Models\UploadJob;
use App\Models\User;
use App\Services\Merchant\UploadPreviewService;
use Illuminate\Support\Collection;

class UploadJobListMapper
{
    public function __construct(
        private readonly UploadPreviewService $uploadPreviewService,
    ) {}

    /**
     * @return list<PrintingListItemData>
     */
    public function listItemsFor(User $user, UploadJobType $type): array
    {
        $jobs = $this->jobsFor($user, $type);

        if ($jobs->isEmpty()) {
            return [];
        }

        $items = [];

        foreach ($jobs as $job) {
            $items[] = $this->toListItem($job);
        }

        return $items;
    }

    /**
     * @return Collection<int, UploadJob>
     */
    public function jobsFor(User $user, UploadJobType $type): Collection
    {
        $merchant = $user->merchant;

        if ($merchant === null) {
            return collect();
        }

        return UploadJob::query()
            ->where('merchant_id', $merchant->id)
            ->where('type', $type)
            ->with(['uploadedBy', 'pdfUploads'])
            ->latest()
            ->limit(50)
            ->get();
    }

    public function toListItem(UploadJob $job): PrintingListItemData
    {
        $previewResult = $this->uploadPreviewService->resolve($job);
        $originalName = $job->metadata['original_names'][0] ?? "#{$job->id}";

        return new PrintingListItemData(
            id: 'upload-job-'.$job->id,
            title: (string) $originalName,
            subtitle: (string) __('merchant.printing.upload_job_subtitle', [
                'id' => $job->id,
                'status' => $job->status?->label() ?? '',
                'date' => $job->created_at?->format('M j, Y H:i') ?? '',
            ]),
            status: $job->status?->value ?? 'pending',
            width: 1500,
            height: 1000,
            preview: $previewResult->preview,
        );
    }
}
