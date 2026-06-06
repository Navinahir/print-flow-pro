<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfTempPath;

/**
 * Manages short-lived paths on the temp disk for upload jobs.
 */
interface PdfTempStorageInterface
{
    public function jobRoot(int $merchantId, int $jobId): PdfTempPath;

    public function sourcesDirectory(int $merchantId, int $jobId): PdfTempPath;

    public function workDirectory(int $merchantId, int $jobId, ?string $suffix = null): PdfTempPath;

    public function outputsDirectory(int $merchantId, int $jobId): PdfTempPath;

    public function absolutePath(PdfTempPath $path): string;

    public function ensureDirectory(PdfTempPath $path): void;

    public function exists(PdfTempPath $path): bool;
}
