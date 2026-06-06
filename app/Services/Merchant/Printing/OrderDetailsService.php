<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing;

use App\DTOs\Merchant\Printing\PrintingListItemData;
use App\Enums\PrintingModule;
use App\Enums\UploadJobType;
use App\Models\User;
use App\Services\Merchant\Preview\OrderDetailsPreviewService;

class OrderDetailsService extends PrintingModuleService
{
    public function __construct(
        private readonly OrderDetailsPreviewService $previewService,
        private readonly UploadJobListMapper $uploadJobListMapper,
    ) {}

    public function module(): PrintingModule
    {
        return PrintingModule::OrderDetails;
    }

    /**
     * @return list<PrintingListItemData>
     */
    protected function listItemsForUser(User $user): array
    {
        $fromUploads = $this->uploadJobListMapper->listItemsFor($user, UploadJobType::OrderPdf);

        if ($fromUploads !== []) {
            return $fromUploads;
        }

        $previewOne = $this->previewService->buildSamplePreview('1')->toArray();
        $previewTwo = $this->previewService->buildSamplePreview('2')->toArray();

        return [
            new PrintingListItemData(
                id: 'order-sample-1',
                title: (string) __('merchant.printing.preview.order_details.samples.list_title', ['id' => '1']),
                subtitle: (string) __('merchant.printing.preview.order_details.samples.list_subtitle'),
                status: 'ready',
                width: 1500,
                height: 1000,
                preview: $previewOne,
            ),
            new PrintingListItemData(
                id: 'order-sample-2',
                title: (string) __('merchant.printing.preview.order_details.samples.list_title', ['id' => '2']),
                subtitle: (string) __('merchant.printing.preview.order_details.samples.list_subtitle_alt'),
                status: 'ready',
                width: 1500,
                height: 1000,
                preview: $previewTwo,
            ),
        ];
    }
}
