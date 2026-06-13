<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\ThermalPdfNormalizationInterface;
use App\DTOs\Merchant\Pdf\PdfCanvasSpec;
use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use App\Services\Merchant\Pdf\Support\ThermalLabelFpdi;

/**
 * Normalizes each thermal source page onto a fixed portrait canvas (100×150 mm by default).
 */
class ThermalPdfNormalizationService implements ThermalPdfNormalizationInterface
{
    public function renderLabelSlot(
        string $sourceAbsolutePath,
        int $pageNumber,
        PdfProcessingContext $context,
        string $outputAbsolutePath,
    ): PdfNormalizationResult {
        [$targetWidthMm, $targetHeightMm] = $this->resolveTargetDimensions($context);

        $pdf = new ThermalLabelFpdi('P', 'mm');
        $pdf->setSourceFile($sourceAbsolutePath);
        $templateId = $pdf->importPage($pageNumber);
        $templateSize = $pdf->getTemplateSize($templateId);

        $sourceWidthMm = $this->normalizeDimensionToMm((float) ($templateSize['width'] ?? 0));
        $sourceHeightMm = $this->normalizeDimensionToMm((float) ($templateSize['height'] ?? 0));

        if ($sourceWidthMm <= 0 || $sourceHeightMm <= 0) {
            throw PdfNormalizationException::failed('Invalid source page dimensions.');
        }

        $pdf->AddPage('P', [$targetWidthMm, $targetHeightMm]);

        $isLandscapeSource = $sourceWidthMm >= $sourceHeightMm;

        if ($isLandscapeSource) {
            $scale = min(
                $targetWidthMm / $sourceHeightMm,
                $targetHeightMm / $sourceWidthMm,
            );
            $placedWidthMm = $sourceHeightMm * $scale;
            $placedHeightMm = $sourceWidthMm * $scale;
            $offsetX = ($targetWidthMm - $placedWidthMm) / 2;
            $offsetY = ($targetHeightMm - $placedHeightMm) / 2;

            $pdf->useImportedPageRotated90Clockwise(
                pageId: $templateId,
                xMm: $offsetX,
                yMm: $offsetY,
                sourceWidthMm: $sourceWidthMm,
                sourceHeightMm: $sourceHeightMm,
                scale: $scale,
            );
        } else {
            $scale = min(
                $targetWidthMm / $sourceWidthMm,
                $targetHeightMm / $sourceHeightMm,
            );
            $placedWidthMm = $sourceWidthMm * $scale;
            $placedHeightMm = $sourceHeightMm * $scale;
            $offsetX = ($targetWidthMm - $placedWidthMm) / 2;
            $offsetY = ($targetHeightMm - $placedHeightMm) / 2;

            $pdf->useTemplate($templateId, $offsetX, $offsetY, $placedWidthMm, $placedHeightMm);
        }

        $this->ensureOutputDirectory($outputAbsolutePath);
        $pdf->Output('F', $outputAbsolutePath);

        return new PdfNormalizationResult(
            implemented: true,
            success: true,
            outputRelativePaths: [],
            metadata: [
                'source_width_mm' => $sourceWidthMm,
                'source_height_mm' => $sourceHeightMm,
                'placed_width_mm' => round($targetWidthMm, 2),
                'placed_height_mm' => round($targetHeightMm, 2),
                'offset_x_mm' => round($offsetX ?? 0.0, 2),
                'offset_y_mm' => round($offsetY ?? 0.0, 2),
                'scale' => round($scale, 4),
                'page_number' => $pageNumber,
                'slot_width_mm' => $targetWidthMm,
                'slot_height_mm' => $targetHeightMm,
                'rotated' => $isLandscapeSource,
                'target_orientation' => 'portrait',
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

    /**
     * @return array{0: float, 1: float}
     */
    private function resolveTargetDimensions(PdfProcessingContext $context): array
    {
        $canvas = $context->canvas ?? $this->defaultCanvasSpec();

        $widthMm = $canvas->widthMm;
        $heightMm = $canvas->heightMm;

        if ($widthMm > $heightMm) {
            [$widthMm, $heightMm] = [$heightMm, $widthMm];
        }

        return [$widthMm, $heightMm];
    }

    private function defaultCanvasSpec(): PdfCanvasSpec
    {
        $canvas = config('pdf.canvas', []);

        $widthMm = (float) ($canvas['width_mm'] ?? 100.0);
        $heightMm = (float) ($canvas['height_mm'] ?? 150.0);

        return new PdfCanvasSpec(
            widthMm: $widthMm,
            heightMm: $heightMm,
            safeZoneInsetMm: (float) ($canvas['safe_zone_inset_mm'] ?? 5.0),
            aspectRatio: $widthMm > 0 && $heightMm > 0 ? $widthMm / $heightMm : 1.0,
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
