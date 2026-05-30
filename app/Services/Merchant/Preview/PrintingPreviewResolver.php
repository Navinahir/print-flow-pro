<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\Enums\PrintingModule;
use App\Models\User;
use App\Services\Merchant\Printing\DeliveryLabelsService;
use App\Services\Merchant\Printing\LogisticsLabelsService;
use App\Services\Merchant\Printing\OrderDetailsService;
use App\Services\Merchant\Printing\PickingListService;

class PrintingPreviewResolver
{
    public function __construct(
        private readonly OrderDetailsService $orderDetailsService,
        private readonly LogisticsLabelsService $logisticsLabelsService,
        private readonly PickingListService $pickingListService,
        private readonly DeliveryLabelsService $deliveryLabelsService,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function resolve(PrintingModule $module, string $itemId, User $user): ?array
    {
        $items = $this->listItemsForModule($module, $user);

        foreach ($items as $item) {
            if ($item->id === $itemId) {
                return $item->preview ?? null;
            }
        }

        return null;
    }

    /**
     * @return list<\App\DTOs\Merchant\Printing\PrintingListItemData|\App\DTOs\Merchant\Printing\DeliveryLabels\DeliveryLabelListItemData>
     */
    private function listItemsForModule(PrintingModule $module, User $user): array
    {
        return match ($module) {
            PrintingModule::OrderDetails => $this->orderDetailsService->previewListItems($user),
            PrintingModule::LogisticsLabels => $this->logisticsLabelsService->previewListItems($user),
            PrintingModule::PickingList => $this->pickingListService->previewListItems($user),
            PrintingModule::DeliveryLabels => $this->deliveryLabelsService->previewListItems($user),
        };
    }
}
