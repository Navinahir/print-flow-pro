<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Printing\DeliveryLabels;

final readonly class DeliveryLabelCsvImportResult
{
    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function __construct(
        public int $uploadJobId,
        public int $importedCount,
        public array $items,
        public array $detectedColumns,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'upload_job_id' => $this->uploadJobId,
            'imported_count' => $this->importedCount,
            'items' => $this->items,
            'detected_columns' => $this->detectedColumns,
            'message' => __('merchant.delivery_labels.csv.import_success', [
                'count' => $this->importedCount,
            ]),
        ];
    }
}
