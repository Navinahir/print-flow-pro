<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Preview;

use App\Contracts\Merchant\Preview\PrintingPreviewPayload;
use App\Enums\PrintingPreviewType;

final readonly class PickingListPreviewData implements PrintingPreviewPayload
{
    /**
     * @param  list<array{sku: string, name: string, location: string, quantity: int}>  $rows
     */
    public function __construct(
        public string $listReference,
        public string $warehouse,
        public string $pickDate,
        public array $rows,
        public int $totalUnits,
    ) {}

    public function type(): PrintingPreviewType
    {
        return PrintingPreviewType::PickingList;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type()->value,
            'list_reference' => $this->listReference,
            'warehouse' => $this->warehouse,
            'pick_date' => $this->pickDate,
            'rows' => $this->rows,
            'total_units' => $this->totalUnits,
        ];
    }
}
