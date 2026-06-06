<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\ThermalA4OutputSpec;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use setasign\Fpdi\Fpdi;

/**
 * Places normalized thermal label slots onto A4 output sheets at native label size.
 */
class ThermalA4SheetComposer
{
    /**
     * @return array<string, mixed>
     */
    public function composeSingle(string $slotAbsolutePath, string $outputAbsolutePath, ?ThermalA4OutputSpec $spec = null): array
    {
        $spec ??= ThermalA4OutputSpec::fromConfig();

        $pdf = $this->createA4Document($spec);
        $placement = $this->placeSlot(
            pdf: $pdf,
            slotAbsolutePath: $slotAbsolutePath,
            xMm: $spec->singlePaddingLeftMm,
            yMm: $spec->singlePaddingTopMm,
        );

        $this->writeOutput($pdf, $outputAbsolutePath);

        return [
            'layout' => 'a4_single',
            'page_width_mm' => $spec->pageWidthMm,
            'page_height_mm' => $spec->pageHeightMm,
            'label_count' => 1,
            'placement' => $placement,
        ];
    }

    /**
     * @param  list<string>  $slotAbsolutePaths  Up to labels_per_page slot PDF paths
     * @return array<string, mixed>
     */
    public function composeMulti(array $slotAbsolutePaths, string $outputAbsolutePath, ?ThermalA4OutputSpec $spec = null): array
    {
        $spec ??= ThermalA4OutputSpec::fromConfig();
        $limit = min(count($slotAbsolutePaths), $spec->labelsPerPage);

        if ($limit === 0) {
            throw PdfNormalizationException::failed('No label slots provided for A4 sheet.');
        }

        $referenceSlot = $this->readSlotDimensions($slotAbsolutePaths[0]);
        $pdf = $this->createA4Document($spec);
        $placements = [];

        for ($index = 0; $index < $limit; $index++) {
            $column = $index % $spec->multiColumns;
            $row = intdiv($index, $spec->multiColumns);
            $slotDimensions = $this->readSlotDimensions($slotAbsolutePaths[$index]);
            $origin = $spec->multiLabelOriginMm(
                labelWidthMm: $referenceSlot['width_mm'],
                labelHeightMm: $referenceSlot['height_mm'],
                column: $column,
                row: $row,
            );

            $placement = $this->placeSlot(
                pdf: $pdf,
                slotAbsolutePath: $slotAbsolutePaths[$index],
                xMm: $origin['x_mm'],
                yMm: $origin['y_mm'],
            );

            $placements[] = [
                'index' => $index,
                'column' => $column,
                'row' => $row,
                ...$placement,
            ];

            if ($slotDimensions['width_mm'] !== $referenceSlot['width_mm']
                || $slotDimensions['height_mm'] !== $referenceSlot['height_mm']) {
                $placements[$index]['slot_size_mm'] = $slotDimensions;
            }
        }

        $this->writeOutput($pdf, $outputAbsolutePath);

        return [
            'layout' => 'a4_multi',
            'page_width_mm' => $spec->pageWidthMm,
            'page_height_mm' => $spec->pageHeightMm,
            'label_count' => $limit,
            'label_width_mm' => $referenceSlot['width_mm'],
            'label_height_mm' => $referenceSlot['height_mm'],
            'placements' => $placements,
        ];
    }

    private function createA4Document(ThermalA4OutputSpec $spec): Fpdi
    {
        $pdf = new Fpdi('P', 'mm');
        $pdf->AddPage('P', [$spec->pageWidthMm, $spec->pageHeightMm]);

        return $pdf;
    }

    /**
     * @return array{width_mm: float, height_mm: float}
     */
    private function readSlotDimensions(string $slotAbsolutePath): array
    {
        $pdf = new Fpdi('P', 'mm');
        $pdf->setSourceFile($slotAbsolutePath);
        $templateSize = $pdf->getTemplateSize($pdf->importPage(1));

        $widthMm = $this->normalizeDimensionToMm((float) ($templateSize['width'] ?? 0));
        $heightMm = $this->normalizeDimensionToMm((float) ($templateSize['height'] ?? 0));

        if ($widthMm <= 0 || $heightMm <= 0) {
            throw PdfNormalizationException::failed('Invalid label slot dimensions.');
        }

        return [
            'width_mm' => $widthMm,
            'height_mm' => $heightMm,
        ];
    }

    /**
     * @return array{x_mm: float, y_mm: float, width_mm: float, height_mm: float}
     */
    private function placeSlot(
        Fpdi $pdf,
        string $slotAbsolutePath,
        float $xMm,
        float $yMm,
    ): array {
        $pdf->setSourceFile($slotAbsolutePath);
        $templateId = $pdf->importPage(1);
        $templateSize = $pdf->getTemplateSize($templateId);

        $placedWidth = $this->normalizeDimensionToMm((float) ($templateSize['width'] ?? 0));
        $placedHeight = $this->normalizeDimensionToMm((float) ($templateSize['height'] ?? 0));

        if ($placedWidth <= 0 || $placedHeight <= 0) {
            throw PdfNormalizationException::failed('Invalid label slot dimensions.');
        }

        $pdf->useTemplate($templateId, $xMm, $yMm, $placedWidth, $placedHeight);

        return [
            'x_mm' => round($xMm, 2),
            'y_mm' => round($yMm, 2),
            'width_mm' => round($placedWidth, 2),
            'height_mm' => round($placedHeight, 2),
        ];
    }

    private function writeOutput(Fpdi $pdf, string $outputAbsolutePath): void
    {
        $directory = dirname($outputAbsolutePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw PdfNormalizationException::failed('Could not create output directory.');
        }

        $pdf->Output('F', $outputAbsolutePath);
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
