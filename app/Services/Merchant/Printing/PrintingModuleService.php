<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing;

use App\Contracts\Merchant\Printing\PrintingModuleServiceInterface;
use App\DTOs\Merchant\Printing\PrintingListItemData;
use App\DTOs\Merchant\Printing\PrintingWorkspaceViewData;
use App\Models\User;

abstract class PrintingModuleService implements PrintingModuleServiceInterface
{
    public function buildWorkspace(User $user): PrintingWorkspaceViewData
    {
        $module = $this->module();
        $key = $module->translationKey();

        return new PrintingWorkspaceViewData(
            module: $module,
            title: (string) __($key.'.title'),
            subtitle: (string) __($key.'.subtitle'),
            listItems: $this->listItemsForUser($user),
            selectedItemId: null,
        );
    }

    /**
     * @return list<PrintingListItemData>
     */
    public function previewListItems(User $user): array
    {
        return $this->listItemsForUser($user);
    }

    /**
     * @return list<PrintingListItemData>
     */
    protected function listItemsForUser(User $user): array
    {
        return [];
    }

    /**
     * @return list<PrintingListItemData>
     */
    protected function placeholderListItems(): array
    {
        return [
            new PrintingListItemData(
                id: 'placeholder-1',
                title: (string) __('merchant.printing.workspace.placeholder_item_title'),
                subtitle: (string) __('merchant.printing.workspace.placeholder_item_subtitle'),
                status: 'pending',
                width: 1500,
                height: 1000,
            ),
            new PrintingListItemData(
                id: 'placeholder-2',
                title: (string) __('merchant.printing.workspace.placeholder_item_invalid_title'),
                subtitle: (string) __('merchant.printing.workspace.placeholder_item_invalid_subtitle'),
                status: 'pending',
                width: 800,
                height: 600,
            ),
        ];
    }
}
