<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\Services\Merchant\Pdf\Support\FpdiDocumentAdapter;
use Tests\TestCase;

class FpdiDocumentAdapterTest extends TestCase
{
    public function test_open_reads_page_size_from_fixture_pdf(): void
    {
        $fixture = base_path('tests/Fixtures/Pdf/thermal_sample.pdf');
        $adapter = app(FpdiDocumentAdapter::class);

        $adapter->open($fixture);
        $size = $adapter->pageSizeMm(1);

        $this->assertSame(1, $adapter->pageCount());
        $this->assertGreaterThan(50.0, $size['width_mm']);
        $this->assertGreaterThan(50.0, $size['height_mm']);

        $adapter->close();
    }
}
