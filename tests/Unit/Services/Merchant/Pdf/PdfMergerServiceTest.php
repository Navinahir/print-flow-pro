<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\Services\Merchant\Pdf\PdfMergerService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Tests\Support\PdfFixtureFactory;
use Tests\TestCase;

class PdfMergerServiceTest extends TestCase
{
    private PdfMergerService $merger;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('temp');
        $this->merger = app(PdfMergerService::class);
    }

    public function test_merges_multiple_pdfs_without_transforming_page_sizes(): void
    {
        $first = 'merge-test/first.pdf';
        $second = 'merge-test/second.pdf';
        $output = 'merge-test/output.pdf';

        PdfFixtureFactory::putMultiPageThermalLabel($first, 2);
        PdfFixtureFactory::putThermalLabel($second);

        $firstAbsolute = Storage::disk('temp')->path($first);
        $secondAbsolute = Storage::disk('temp')->path($second);
        $outputAbsolute = Storage::disk('temp')->path($output);

        $result = $this->merger->mergeToFile([$firstAbsolute, $secondAbsolute], $outputAbsolute);

        $this->assertSame(3, $result['page_count']);
        $this->assertCount(3, $result['pages']);
        $this->assertFileExists($outputAbsolute);

        $merged = new Fpdi('P', 'mm');
        $this->assertSame(3, $merged->setSourceFile($outputAbsolute));

        for ($page = 1; $page <= 3; $page++) {
            $templateId = $merged->importPage($page);
            $size = $merged->getTemplateSize($templateId);
            $widthMm = round((float) $size['width'], 2);
            $heightMm = round((float) $size['height'], 2);

            $this->assertSame(100.0, $widthMm);
            $this->assertSame(150.0, $heightMm);
        }
    }

    public function test_preserves_a4_page_dimensions_from_shopee_order_pdfs(): void
    {
        $first = 'merge-test/a4-first.pdf';
        $second = 'merge-test/a4-second.pdf';
        $output = 'merge-test/a4-output.pdf';

        PdfFixtureFactory::putA4Label($first);
        PdfFixtureFactory::putA4Label($second);

        $outputAbsolute = Storage::disk('temp')->path($output);

        $this->merger->mergeToFile(
            [
                Storage::disk('temp')->path($first),
                Storage::disk('temp')->path($second),
            ],
            $outputAbsolute,
        );

        $merged = new Fpdi('P', 'mm');
        $this->assertSame(2, $merged->setSourceFile($outputAbsolute));

        for ($page = 1; $page <= 2; $page++) {
            $templateId = $merged->importPage($page);
            $size = $merged->getTemplateSize($templateId);

            $this->assertEqualsWithDelta(210.0, (float) $size['width'], 1.0);
            $this->assertEqualsWithDelta(297.0, (float) $size['height'], 1.0);
        }
    }

    protected function tearDown(): void
    {
        $testingRoot = storage_path('framework/testing/disks/temp/merge-test');

        if (is_dir($testingRoot)) {
            File::deleteDirectory($testingRoot);
        }

        parent::tearDown();
    }
}
