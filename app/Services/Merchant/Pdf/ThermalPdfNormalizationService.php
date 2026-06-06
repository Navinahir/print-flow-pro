<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\ThermalPdfNormalizationInterface;
use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use setasign\Fpdi\Fpdi;

/**
 * Copies each thermal source page into a native-size slot PDF (top-left, no scaling).
 */
class ThermalPdfNormalizationService implements ThermalPdfNormalizationInterface
{
    public function renderLabelSlot(
        string $sourceAbsolutePath,
        int $pageNumber,
        PdfProcessingContext $context,
        string $outputAbsolutePath,
    ): PdfNormalizationResult {
        $pdf = new Fpdi('P', 'mm');
        $pdf->setSourceFile($sourceAbsolutePath);
        $templateId = $pdf->importPage($pageNumber);
        $templateSize = $pdf->getTemplateSize($templateId);

        $sourceWidthMm = $this->normalizeDimensionToMm((float) ($templateSize['width'] ?? 0));
        $sourceHeightMm = $this->normalizeDimensionToMm((float) ($templateSize['height'] ?? 0));

        if ($sourceWidthMm <= 0 || $sourceHeightMm <= 0) {
            throw PdfNormalizationException::failed('Invalid source page dimensions.');
        }

        $orientation = $sourceWidthMm >= $sourceHeightMm ? 'L' : 'P';
        $pdf->AddPage($orientation, [$sourceWidthMm, $sourceHeightMm]);
        $pdf->useTemplate($templateId, 0.0, 0.0, $sourceWidthMm, $sourceHeightMm);

        $this->ensureOutputDirectory($outputAbsolutePath);
        $pdf->Output('F', $outputAbsolutePath);

        return new PdfNormalizationResult(
            implemented: true,
            success: true,
            outputRelativePaths: [],
            metadata: [
                'source_width_mm' => $sourceWidthMm,
                'source_height_mm' => $sourceHeightMm,
                'placed_width_mm' => round($sourceWidthMm, 2),
                'placed_height_mm' => round($sourceHeightMm, 2),
                'offset_x_mm' => 0.0,
                'offset_y_mm' => 0.0,
                'scale' => 1.0,
                'page_number' => $pageNumber,
                'slot_width_mm' => $sourceWidthMm,
                'slot_height_mm' => $sourceHeightMm,
            ],
            message: __('merchant.pdf.normalization.thermal_page_complete'),
        );
    }

    public function normalizePage(
        string $sourceAbsolutePath,
        int $pageNumber,
        PdfProcessingContext $context,
        string $outputAbsolutePath,
    ): PdfNormalizationResult {
        return $this->renderLabelSlot(
            sourceAbsolutePath: $sourceAbsolutePath,
            pageNumber: $pageNumber,
            context: $context,
            outputAbsolutePath: $outputAbsolutePath,
        );
    }

    private function ensureOutputDirectory(string $outputAbsolutePath): void
    {
        $directory = dirname($outputAbsolutePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw PdfNormalizationException::failed('Could not create output directory.');
        }
    }

    private function normalizeDimensionToMm(float $value): float
    {
        if ($value <= 0) {
            return 0.0;
        }

        if ($value > 200) {
            return round($value * 25.4 / 72, 2);
        }

        return round($value, 2);
    }
}
