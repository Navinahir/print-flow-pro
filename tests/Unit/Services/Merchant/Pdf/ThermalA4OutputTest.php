<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingMode;
use App\Services\Merchant\Pdf\ThermalA4SheetComposer;
use App\Services\Merchant\Pdf\ThermalPdfNormalizationService;
use setasign\Fpdi\Fpdi;
use Tests\Support\PdfFixtureFactory;
use Tests\TestCase;

class ThermalA4OutputTest extends TestCase
{
    public function test_single_thermal_label_outputs_a4_page(): void
    {
        $source = storage_path('framework/testing/a4-single-source.pdf');
        $slot = storage_path('framework/testing/a4-single-slot.pdf');
        $output = storage_path('framework/testing/a4-single-output.pdf');

        PdfFixtureFactory::createThermalLabelPdf($source, 100.0, 150.0);
        PdfFixtureFactory::createThermalLabelPdf($slot, 150.0, 100.0);

        $composer = app(ThermalA4SheetComposer::class);
        $metadata = $composer->composeSingle($slot, $output);

        $this->assertSame('a4_single', $metadata['layout']);
        $this->assertFileExists($output);
        $this->assertSame([210.0, 297.0], $this->pageSizeMm($output));
    }

    public function test_multiple_thermal_labels_output_one_a4_sheet_with_four_quadrants(): void
    {
        $slots = [];
        $output = storage_path('framework/testing/a4-multi-output.pdf');

        for ($i = 0; $i < 3; $i++) {
            $slot = storage_path("framework/testing/a4-multi-slot-{$i}.pdf");
            PdfFixtureFactory::createThermalLabelPdf($slot, 150.0, 100.0);
            $slots[] = $slot;
        }

        $composer = app(ThermalA4SheetComposer::class);
        $metadata = $composer->composeMulti($slots, $output);

        $this->assertSame('a4_multi', $metadata['layout']);
        $this->assertSame(3, $metadata['label_count']);
        $this->assertCount(3, $metadata['placements']);
        $this->assertFileExists($output);
        $this->assertSame([210.0, 297.0], $this->pageSizeMm($output));
    }

    public function test_render_label_slot_produces_landscape_canvas(): void
    {
        $source = storage_path('framework/testing/label-slot-source.pdf');
        $slot = storage_path('framework/testing/label-slot-output.pdf');
        PdfFixtureFactory::createThermalLabelPdf($source, 100.0, 150.0);

        $context = new PdfProcessingContext(
            uploadJobId: 1,
            merchantId: 1,
            countryCode: 'TW',
            mode: PdfProcessingMode::ThermalLabel,
        );

        $result = app(ThermalPdfNormalizationService::class)->renderLabelSlot(
            sourceAbsolutePath: $source,
            pageNumber: 1,
            context: $context,
            outputAbsolutePath: $slot,
        );

        $this->assertTrue($result->success);
        $this->assertSame([100.0, 150.0], $this->pageSizeMm($slot));
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function pageSizeMm(string $absolutePath): array
    {
        $pdf = new Fpdi;
        $pdf->setSourceFile($absolutePath);
        $templateId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($templateId);

        return [
            round((float) ($size['width'] ?? 0), 1),
            round((float) ($size['height'] ?? 0), 1),
        ];
    }
}
