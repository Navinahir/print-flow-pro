<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\Services\Merchant\Pdf\PdfCanvasService;
use Tests\TestCase;

class PdfCanvasServiceTest extends TestCase
{
    public function test_build_canvas_spec_uses_preview_configuration(): void
    {
        $canvas = app(PdfCanvasService::class)->buildCanvasSpec();

        $this->assertSame(100.0, $canvas->widthMm);
        $this->assertSame(150.0, $canvas->heightMm);
        $this->assertSame(5.0, $canvas->safeZoneInsetMm);
    }

    public function test_safe_area_subtracts_double_inset(): void
    {
        $canvas = app(PdfCanvasService::class)->buildCanvasSpec();
        $safeArea = app(PdfCanvasService::class)->safeAreaDimensions($canvas);

        $this->assertSame(90.0, $safeArea->widthMm);
        $this->assertSame(140.0, $safeArea->heightMm);
    }
}
