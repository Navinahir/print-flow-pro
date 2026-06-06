<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\DTOs\Merchant\Upload\UploadPreviewResult;
use App\Enums\PrintJobStatus;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\DeliveryLabel;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Services\Merchant\Preview\LogisticsLabelsPreviewService;
use App\Services\Merchant\Preview\OrderDetailsPreviewService;
use App\Services\Merchant\Preview\PickingListPreviewService;
use App\Services\Merchant\Printing\DeliveryLabels\DeliveryLabelCsvImportService;
use App\Services\Merchant\Printing\DeliveryLabels\DeliveryLabelPreviewService;

class UploadPreviewService
{
    public function __construct(
        private readonly OrderDetailsPreviewService $orderDetailsPreviewService,
        private readonly LogisticsLabelsPreviewService $logisticsLabelsPreviewService,
        private readonly PickingListPreviewService $pickingListPreviewService,
        private readonly DeliveryLabelPreviewService $deliveryLabelPreviewService,
        private readonly DeliveryLabelCsvImportService $deliveryLabelCsvImportService,
        private readonly UploadShowViewService $uploadShowViewService,
    ) {}

    public function resolve(UploadJob $job, ?string $itemId = null): UploadPreviewResult
    {
        $job->loadMissing(['pdfUploads', 'deliveryLabels', 'printJobs']);

        if ($job->type === UploadJobType::ThermalLabel) {
            return $this->resolveThermalLabelPreview($job, $itemId);
        }

        $preview = $this->buildPreviewPayload($job);

        return new UploadPreviewResult(
            available: $preview !== null,
            preview: $preview,
            previewType: is_array($preview) ? ($preview['type'] ?? null) : null,
        );
    }

    private function resolveThermalLabelPreview(UploadJob $job, ?string $itemId): UploadPreviewResult
    {
        if ($job->status === UploadStatus::Failed) {
            return new UploadPreviewResult(
                available: false,
                preview: null,
                previewType: null,
                statusMessage: $job->error_message ?? __('merchant.uploads.preview.processing_failed'),
            );
        }

        if (in_array($job->status, [UploadStatus::Pending, UploadStatus::Processing], true)) {
            return new UploadPreviewResult(
                available: false,
                preview: null,
                previewType: null,
                statusMessage: __('merchant.uploads.preview.processing'),
            );
        }

        $printJobs = $job->printJobs()
            ->whereIn('status', [PrintJobStatus::Ready, PrintJobStatus::Downloaded])
            ->orderBy('id')
            ->get();

        if ($printJobs->isEmpty()) {
            return new UploadPreviewResult(
                available: false,
                preview: null,
                previewType: null,
                statusMessage: __('merchant.uploads.preview.empty_description'),
            );
        }

        $items = $printJobs
            ->map(fn (PrintJob $printJob): array => $this->printJobItem($printJob, $job, $printJobs))
            ->all();
        $selected = $this->selectItem($items, $itemId) ?? $items[0];

        return new UploadPreviewResult(
            available: true,
            preview: $selected['preview'],
            previewType: $selected['preview']['type'] ?? null,
            items: $items,
            selectedItemId: $selected['id'],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>|null
     */
    private function selectItem(array $items, ?string $itemId): ?array
    {
        if ($itemId === null) {
            return $items[0] ?? null;
        }

        foreach ($items as $item) {
            if (($item['id'] ?? null) === $itemId) {
                return $item;
            }
        }

        return $items[0] ?? null;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PrintJob>  $allPrintJobs
     * @return array<string, mixed>
     */
    private function printJobItem(PrintJob $printJob, UploadJob $uploadJob, \Illuminate\Support\Collection $allPrintJobs): array
    {
        $formatted = $this->uploadShowViewService->formatPrintOutput($printJob, $uploadJob, $allPrintJobs);
        $preview = $this->logisticsLabelsPreviewService->buildFromPrintJob($printJob)->toArray();

        return [
            'id' => $formatted['list_id'],
            'title' => $formatted['title'],
            'subtitle' => $formatted['layout_label'].' · '.$formatted['size_label'],
            'preview' => array_merge($preview, [
                'preview_url' => $formatted['preview_url'],
            ]),
            'download_url' => $formatted['download_url'],
            'preview_url' => $formatted['preview_url'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildPreviewPayload(UploadJob $job): ?array
    {
        return match ($job->type) {
            UploadJobType::OrderPdf => $this->orderDetailsPreviewService
                ->buildSamplePreview((string) $job->id)
                ->toArray(),
            UploadJobType::PickingList => $this->pickingListPreviewService
                ->buildSamplePreview((string) $job->id)
                ->toArray(),
            UploadJobType::DeliveryLabel => $this->buildDeliveryLabelPreview($job),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildDeliveryLabelPreview(UploadJob $job): ?array
    {
        /** @var DeliveryLabel|null $label */
        $label = $job->deliveryLabels->first();

        if ($label !== null) {
            $preview = $this->deliveryLabelCsvImportService
                ->buildListItemFromModel($label, 1)
                ->preview;

            return $preview?->toArray();
        }

        return $this->deliveryLabelPreviewService
            ->buildPreview(
                recipientName: (string) __('merchant.delivery_labels.samples.short_recipient'),
                courierAddress: (string) __('merchant.delivery_labels.samples.short_address'),
                remarks: (string) __('merchant.delivery_labels.samples.short_remarks'),
            )
            ->toArray();
    }
}
