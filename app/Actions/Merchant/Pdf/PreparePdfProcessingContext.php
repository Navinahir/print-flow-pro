<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingMode;
use App\Models\UploadJob;

/**
 * Builds a PdfProcessingContext from an UploadJob and its stored source files.
 */
class PreparePdfProcessingContext
{
    public function execute(UploadJob $uploadJob): PdfProcessingContext
    {
        $uploadJob->loadMissing(['pdfUploads', 'merchant']);

        $sourcePaths = $uploadJob->pdfUploads
            ->pluck('path')
            ->filter(static fn (?string $path): bool => is_string($path) && $path !== '')
            ->values()
            ->all();

        $metadata = $uploadJob->metadata ?? [];

        if (isset($metadata['spreadsheet_files']) && is_array($metadata['spreadsheet_files'])) {
            foreach ($metadata['spreadsheet_files'] as $file) {
                if (is_array($file) && isset($file['path']) && is_string($file['path'])) {
                    $sourcePaths[] = $file['path'];
                }
            }
        }

        if (isset($metadata['csv_path']) && is_string($metadata['csv_path'])) {
            $sourcePaths[] = $metadata['csv_path'];
        }

        return new PdfProcessingContext(
            uploadJobId: $uploadJob->id,
            merchantId: (int) $uploadJob->merchant_id,
            countryCode: (string) ($uploadJob->country_code ?? $uploadJob->merchant?->country_code ?? 'TW'),
            mode: PdfProcessingMode::fromUploadJobType($uploadJob->type),
            sourceRelativePaths: array_values(array_unique($sourcePaths)),
        );
    }
}
