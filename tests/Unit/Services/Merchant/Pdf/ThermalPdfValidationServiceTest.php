<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfBoundaryBox;
use App\Enums\PdfValidationCode;
use App\Services\Merchant\Pdf\ThermalPdfValidationService;
use Tests\TestCase;

class ThermalPdfValidationServiceTest extends TestCase
{
    public function test_accepts_standard_thermal_label_dimensions(): void
    {
        $boundary = new PdfBoundaryBox(
            pageNumber: 1,
            widthMm: 100.0,
            heightMm: 150.0,
        );

        $result = app(ThermalPdfValidationService::class)->validateBoundary($boundary);

        $this->assertTrue($result->passed);
    }

    public function test_rejects_a4_dimensions(): void
    {
        $boundary = new PdfBoundaryBox(
            pageNumber: 1,
            widthMm: 210.0,
            heightMm: 297.0,
        );

        $result = app(ThermalPdfValidationService::class)->validateBoundary($boundary);

        $this->assertFalse($result->passed);
        $this->assertContains(PdfValidationCode::A4Rejected, $result->codes);
    }

    public function test_rejects_unsupported_thermal_sizes(): void
    {
        $boundary = new PdfBoundaryBox(
            pageNumber: 1,
            widthMm: 50.0,
            heightMm: 50.0,
        );

        $result = app(ThermalPdfValidationService::class)->validateBoundary($boundary);

        $this->assertFalse($result->passed);
        $this->assertContains(PdfValidationCode::ThermalSizeRejected, $result->codes);
    }
}
