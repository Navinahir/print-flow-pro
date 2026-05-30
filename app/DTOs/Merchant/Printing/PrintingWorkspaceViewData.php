<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Printing;

use App\Enums\PrintingModule;

final readonly class PrintingWorkspaceViewData
{
    /**
     * @param  list<PrintingListItemData>  $listItems
     */
    public function __construct(
        public PrintingModule $module,
        public string $title,
        public string $subtitle,
        public array $listItems,
        public ?string $selectedItemId = null,
    ) {}

    public function selectedItem(): ?PrintingListItemData
    {
        if ($this->selectedItemId === null) {
            return null;
        }

        foreach ($this->listItems as $item) {
            if ($item->id === $this->selectedItemId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listItemsAsArrays(): array
    {
        return array_map(
            fn (PrintingListItemData $item): array => $item->toArray(),
            $this->listItems,
        );
    }
}
