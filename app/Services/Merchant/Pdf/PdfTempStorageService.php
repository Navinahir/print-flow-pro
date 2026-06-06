<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfTempStorageInterface;
use App\DTOs\Merchant\Pdf\PdfTempPath;
use App\Exceptions\Merchant\Pdf\PdfStorageException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Short-lived path builder for upload jobs on the temp disk.
 */
class PdfTempStorageService implements PdfTempStorageInterface
{
    public function jobRoot(int $merchantId, int $jobId): PdfTempPath
    {
        return $this->pathFromTemplate('paths.job_root', $merchantId, $jobId);
    }

    public function sourcesDirectory(int $merchantId, int $jobId): PdfTempPath
    {
        return $this->pathFromTemplate('paths.sources', $merchantId, $jobId);
    }

    public function workDirectory(int $merchantId, int $jobId, ?string $suffix = null): PdfTempPath
    {
        $path = $this->pathFromTemplate('paths.work', $merchantId, $jobId);

        if ($suffix !== null && $suffix !== '') {
            return $path->withSuffix($suffix);
        }

        return $path;
    }

    public function outputsDirectory(int $merchantId, int $jobId): PdfTempPath
    {
        return $this->pathFromTemplate('paths.outputs', $merchantId, $jobId);
    }

    public function absolutePath(PdfTempPath $path): string
    {
        return $this->disk($path->disk)->path($path->relativePath);
    }

    public function ensureDirectory(PdfTempPath $path): void
    {
        $disk = $this->disk($path->disk);

        if ($disk->exists($path->relativePath)) {
            return;
        }

        if (! $disk->makeDirectory($path->relativePath)) {
            throw PdfStorageException::directoryCreationFailed($path->relativePath);
        }
    }

    public function exists(PdfTempPath $path): bool
    {
        return $this->disk($path->disk)->exists($path->relativePath);
    }

    private function pathFromTemplate(string $configKey, int $merchantId, int $jobId): PdfTempPath
    {
        $template = (string) config("pdf.{$configKey}", 'merchants/{merchant_id}/jobs/{job_id}');
        $relative = str_replace(
            ['{merchant_id}', '{job_id}'],
            [(string) $merchantId, (string) $jobId],
            $template,
        );

        return new PdfTempPath(
            disk: (string) config('pdf.temp_disk', 'temp'),
            relativePath: $relative,
        );
    }

    private function disk(string $name): Filesystem
    {
        return Storage::disk($name);
    }
}
