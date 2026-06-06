<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfTempStorageInterface;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfTempStorageServiceTest extends TestCase
{
    public function test_builds_merchant_job_paths_on_temp_disk(): void
    {
        Storage::fake('temp');

        $storage = app(PdfTempStorageInterface::class);
        $work = $storage->workDirectory(merchantId: 7, jobId: 42, suffix: 'pipeline');

        $this->assertSame('temp', $work->disk);
        $this->assertSame('merchants/7/jobs/42/work/pipeline', $work->relativePath);

        $storage->ensureDirectory($work);

        Storage::disk('temp')->assertExists('merchants/7/jobs/42/work/pipeline');
    }
}
