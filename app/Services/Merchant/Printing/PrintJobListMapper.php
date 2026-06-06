<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing;

use App\DTOs\Merchant\Printing\PrintingListItemData;
use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use App\Models\User;
use App\Services\Merchant\Preview\LogisticsLabelsPreviewService;
use Illuminate\Support\Collection;

class PrintJobListMapper
{
    public function __construct(
        private readonly LogisticsLabelsPreviewService $previewService,
    ) {}

    /**
     * @return list<PrintingListItemData>
     */
    public function listItemsFor(User $user, string $module = 'logistics_labels'): array
    {
        $jobs = $this->printJobsFor($user, $module);

        if ($jobs->isEmpty()) {
            return [];
        }

        $items = [];

        foreach ($jobs as $printJob) {
            $items[] = $this->toListItem($printJob);
        }

        return $items;
    }

    /**
     * @return Collection<int, PrintJob>
     */
    public function printJobsFor(User $user, string $module = 'logistics_labels'): Collection
    {
        $merchant = $user->merchant;

        if ($merchant === null) {
            return collect();
        }

        return PrintJob::query()
            ->where('merchant_id', $merchant->id)
            ->where('module', $module)
            ->with(['uploadJob', 'pdfUpload'])
            ->latest()
            ->limit(100)
            ->get();
    }

    public function toListItem(PrintJob $printJob): PrintingListItemData
    {
        $preview = $this->previewService->buildFromPrintJob($printJob)->toArray();
        $originalName = (string) (
            $printJob->metadata['original_name']
            ?? $printJob->pdfUpload?->original_name
            ?? __('merchant.printing.logistics_labels.list.default_title', ['id' => $printJob->id])
        );

        return new PrintingListItemData(
            id: 'print-job-'.$printJob->id,
            title: $originalName,
            subtitle: (string) __('merchant.printing.logistics_labels.list.subtitle', [
                'page' => $printJob->source_page_number,
                'status' => $printJob->status->label(),
                'date' => $printJob->created_at?->format('M j, Y H:i') ?? '',
            ]),
            status: $this->mapStatus($printJob->status),
            meta: $preview['download_url'] ?? null,
            width: 1500,
            height: 1000,
            preview: $preview,
        );
    }

    private function mapStatus(PrintJobStatus $status): string
    {
        return match ($status) {
            PrintJobStatus::Ready, PrintJobStatus::Downloaded => 'ready',
            PrintJobStatus::Failed => 'failed',
            PrintJobStatus::Processing => 'processing',
            default => 'pending',
        };
    }
}
