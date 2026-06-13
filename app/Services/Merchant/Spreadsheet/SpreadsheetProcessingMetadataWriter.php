<?php

declare(strict_types=1);

namespace App\Services\Merchant\Spreadsheet;

use App\Models\UploadJob;

class SpreadsheetProcessingMetadataWriter
{
    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path?: string}>  $sourceFiles
     * @param  list<array{source_path: string, status: string, error_message: string|null}>  $processingResults
     */
    public function record(int $uploadJobId, array $sourceFiles, array $processingResults): void
    {
        if ($processingResults === [] && count($sourceFiles) === 1) {
            $processingResults = [[
                'source_path' => $sourceFiles[0]['path'],
                'status' => 'completed',
                'error_message' => null,
            ]];
        }

        if ($processingResults === [] && count($sourceFiles) > 1) {
            $processingResults = array_map(static fn (array $source): array => [
                'source_path' => $source['path'],
                'status' => 'completed',
                'error_message' => null,
            ], $sourceFiles);
        }

        if ($processingResults === []) {
            return;
        }

        $uploadJob = UploadJob::query()->find($uploadJobId);

        if ($uploadJob === null) {
            return;
        }

        $metadata = $uploadJob->metadata ?? [];
        $metadata['spreadsheet_processing'] = $processingResults;
        $uploadJob->update(['metadata' => $metadata]);
    }
}
