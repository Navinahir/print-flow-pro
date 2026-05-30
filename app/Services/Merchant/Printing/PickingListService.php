<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing;

use App\DTOs\Merchant\Printing\PrintingListItemData;
use App\Enums\PrintingModule;
use App\Models\User;
use App\Services\Merchant\Preview\PickingListPreviewService;

class PickingListService extends PrintingModuleService
{
    public function __construct(
        private readonly PickingListPreviewService $previewService,
    ) {}

    public function module(): PrintingModule
    {
        return PrintingModule::PickingList;
    }

    /**
     * @return list<PrintingListItemData>
     */
    protected function listItemsForUser(User $user): array
    {
        $preview = $this->previewService->buildSamplePreview('1')->toArray();

        return [
            new PrintingListItemData(
                id: 'picking-sample-1',
                title: (string) __('merchant.printing.preview.picking_list.samples.list_title'),
                subtitle: (string) __('merchant.printing.preview.picking_list.samples.list_subtitle'),
                status: 'ready',
                width: 1500,
                height: 1000,
                preview: $preview,
            ),
        ];
    }
}
