<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing;

use App\DTOs\Merchant\Printing\DeliveryLabels\DeliveryLabelListItemData;
use App\Enums\PrintingModule;
use App\Models\DeliveryLabel;
use App\Models\User;
use App\Services\Merchant\Printing\DeliveryLabels\DeliveryLabelCsvImportService;
use App\Services\Merchant\Printing\DeliveryLabels\DeliveryLabelPreviewService;

class DeliveryLabelsService extends PrintingModuleService
{
    public function __construct(
        private readonly DeliveryLabelPreviewService $previewService,
        private readonly DeliveryLabelCsvImportService $csvImportService,
    ) {}

    public function module(): PrintingModule
    {
        return PrintingModule::DeliveryLabels;
    }

    public function buildDeliveryWorkspace(User $user): \App\DTOs\Merchant\Printing\DeliveryLabels\DeliveryLabelsWorkspaceViewData
    {
        $module = $this->module();
        $key = $module->translationKey();

        return new \App\DTOs\Merchant\Printing\DeliveryLabels\DeliveryLabelsWorkspaceViewData(
            module: $module,
            title: (string) __($key.'.title'),
            subtitle: (string) __($key.'.subtitle'),
            listItems: $this->listItemsForUser($user),
            selectedItemId: null,
        );
    }

    /**
     * @return list<DeliveryLabelListItemData>
     */
    protected function listItemsForUser(User $user): array
    {
        $merchant = $user->merchant;

        if ($merchant === null) {
            return $this->sampleDeliveryLabels();
        }

        $labels = DeliveryLabel::query()
            ->where('merchant_id', $merchant->id)
            ->latest()
            ->limit(50)
            ->get();

        if ($labels->isEmpty()) {
            return $this->sampleDeliveryLabels();
        }

        $items = [];

        foreach ($labels as $index => $label) {
            $items[] = $this->csvImportService->buildListItemFromModel($label, $index + 1);
        }

        return $items;
    }

    /**
     * @return list<DeliveryLabelListItemData>
     */
    private function sampleDeliveryLabels(): array
    {
        $shortAddress = (string) __('merchant.delivery_labels.samples.short_address');
        $longAddress = (string) __('merchant.delivery_labels.samples.long_address');
        $multiLineAddress = (string) __('merchant.delivery_labels.samples.multiline_address');

        return [
            new DeliveryLabelListItemData(
                id: 'delivery-sample-short',
                title: (string) __('merchant.delivery_labels.samples.short_title'),
                subtitle: (string) __('merchant.delivery_labels.samples.short_subtitle'),
                status: 'ready',
                width: 1500,
                height: 1000,
                preview: $this->previewService->buildPreview(
                    recipientName: (string) __('merchant.delivery_labels.samples.short_recipient'),
                    courierAddress: $shortAddress,
                    remarks: (string) __('merchant.delivery_labels.samples.short_remarks'),
                ),
            ),
            new DeliveryLabelListItemData(
                id: 'delivery-sample-long',
                title: (string) __('merchant.delivery_labels.samples.long_title'),
                subtitle: (string) __('merchant.delivery_labels.samples.long_subtitle'),
                status: 'ready',
                width: 1500,
                height: 1000,
                preview: $this->previewService->buildPreview(
                    recipientName: (string) __('merchant.delivery_labels.samples.long_recipient'),
                    courierAddress: $longAddress,
                    remarks: (string) __('merchant.delivery_labels.samples.long_remarks'),
                ),
            ),
            new DeliveryLabelListItemData(
                id: 'delivery-sample-multiline',
                title: (string) __('merchant.delivery_labels.samples.multiline_title'),
                subtitle: (string) __('merchant.delivery_labels.samples.multiline_subtitle'),
                status: 'ready',
                width: 1500,
                height: 1000,
                preview: $this->previewService->buildPreview(
                    recipientName: (string) __('merchant.delivery_labels.samples.multiline_recipient'),
                    courierAddress: $multiLineAddress,
                    remarks: (string) __('merchant.delivery_labels.samples.multiline_remarks'),
                ),
            ),
        ];
    }
}
