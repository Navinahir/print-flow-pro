<?php

declare(strict_types=1);

namespace App\Services\Merchant\Upload;

use App\Models\UploadJob;

class UploadSpreadsheetFileResolver
{
    /**
     * @return array{original_name: string, disk: string, path: string, size_bytes: int}
     */
    public function resolve(UploadJob $uploadJob, int $index): array
    {
        $spreadsheetFiles = is_array($uploadJob->metadata['spreadsheet_files'] ?? null)
            ? $uploadJob->metadata['spreadsheet_files']
            : [];

        if (! isset($spreadsheetFiles[$index]) || ! is_array($spreadsheetFiles[$index])) {
            abort(404);
        }

        $file = $spreadsheetFiles[$index];
        $path = (string) ($file['path'] ?? '');
        $disk = (string) ($file['disk'] ?? 'temp');

        if ($path === '') {
            abort(404);
        }

        return [
            'original_name' => (string) ($file['original_name'] ?? basename($path)),
            'disk' => $disk,
            'path' => $path,
            'size_bytes' => (int) ($file['size_bytes'] ?? 0),
        ];
    }
}
