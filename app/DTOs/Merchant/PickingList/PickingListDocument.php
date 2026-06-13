<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\PickingList;

final readonly class PickingListDocument
{
    /**
     * @param  list<PickingListRow>  $rows
     * @param  list<string>  $sourceFiles
     */
    public function __construct(
        public string $accountName,
        public string $generatedAt,
        public array $rows,
        public array $sourceFiles,
        public int $totalUnits,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'account_name' => $this->accountName,
            'generated_at' => $this->generatedAt,
            'rows' => array_map(static fn (PickingListRow $row): array => $row->toArray(), $this->rows),
            'source_files' => $this->sourceFiles,
            'total_units' => $this->totalUnits,
            'row_count' => count($this->rows),
        ];
    }
}
