<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\Services\Merchant\Pdf\PdfConfigurationService;
use Tests\TestCase;

class PdfConfigurationServiceTest extends TestCase
{
    public function test_configuration_merges_pdf_config_and_preview_settings(): void
    {
        $configuration = app(PdfConfigurationService::class)->configuration();

        $this->assertSame('temp', $configuration->tempDisk);
        $this->assertSame(10, $configuration->outputTtlMinutes);
        $this->assertSame(150.0, $configuration->canvas->widthMm);
        $this->assertSame(210.0, $configuration->a4WidthMm);
    }
}
