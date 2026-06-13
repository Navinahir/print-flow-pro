<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Spreadsheet;

use App\DTOs\Merchant\PickingList\PickingListRow;

final readonly class SpreadsheetBatchParseResult
{
    /**
     * @param  list<PickingListRow>  $rows
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $successfulFiles
     * @param  list<array{source_path: string, status: string, error_message: string|null}>  $processingResults
     * @param  list<array{source_name: string, source_path: string, message: string}>  $fileErrors
     */
    public function __construct(
        public array $rows,
        public array $successfulFiles,
        public array $processingResults,
        public array $fileErrors,
    ) {}
}
