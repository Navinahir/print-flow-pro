<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing;

use App\DTOs\Merchant\Printing\PrintingListItemData;
use App\Enums\PrintingModule;
use App\Models\User;
use App\Services\Merchant\Preview\LogisticsLabelsPreviewService;

class LogisticsLabelsService extends PrintingModuleService
{
    public function __construct(
        private readonly LogisticsLabelsPreviewService $previewService,
    ) {}

    public function module(): PrintingModule
    {
        return PrintingModule::LogisticsLabels;
    }

    /**
     * @return list<PrintingListItemData>
     */
    protected function listItemsForUser(User $user): array
    {
        $preview = $this->previewService->buildSamplePreview('1')->toArray();

        return [
            new PrintingListItemData(
                id: 'logistics-sample-1',
                title: (string) __('merchant.printing.preview.logistics_labels.samples.list_title'),
                subtitle: (string) __('merchant.printing.preview.logistics_labels.samples.list_subtitle'),
                status: 'ready',
                width: 1500,
                height: 1000,
                preview: $preview,
            ),
        ];
    }
}
