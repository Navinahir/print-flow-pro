<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\DTOs\Merchant\Upload\UploadPreviewResult;
use App\Enums\UploadJobType;
use App\Models\DeliveryLabel;
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
    ) {}

    public function resolve(UploadJob $job): UploadPreviewResult
    {
        $job->loadMissing(['pdfUploads', 'deliveryLabels']);

        $preview = $this->buildPreviewPayload($job);

        return new UploadPreviewResult(
            available: $preview !== null,
            preview: $preview,
            previewType: $preview['type'] ?? null,
        );
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
            UploadJobType::ThermalLabel => $this->logisticsLabelsPreviewService
                ->buildSamplePreview((string) $job->id)
                ->toArray(),
            UploadJobType::PickingList => $this->pickingListPreviewService
                ->buildSamplePreview((string) $job->id)
                ->toArray(),
            UploadJobType::DeliveryLabel => $this->buildDeliveryLabelPreview($job),
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
