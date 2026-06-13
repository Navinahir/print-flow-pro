<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use setasign\Fpdi\Fpdi;

/**
 * Appends PDF pages into a single document without scaling, cropping, or re-layout.
 */
class PdfMergerService
{
    /**
     * @param  list<string>  $sourceAbsolutePaths  Ordered list of PDF files to concatenate
     * @return array{page_count: int, pages: list<array{source_index: int, page: int, width_mm: float, height_mm: float}>}
     */
    public function mergeToFile(array $sourceAbsolutePaths, string $outputAbsolutePath): array
    {
        if ($sourceAbsolutePaths === []) {
            throw PdfNormalizationException::failed('No PDF sources provided for merge.');
        }

        $pdf = new Fpdi(
            (string) config('pdf.fpdi.default_orientation', 'P'),
            (string) config('pdf.fpdi.unit', 'mm'),
        );

        $pageLog = [];
        $totalPages = 0;

        foreach ($sourceAbsolutePaths as $sourceIndex => $sourcePath) {
            if (! is_readable($sourcePath)) {
                throw PdfNormalizationException::failed("PDF source is not readable: {$sourcePath}");
            }

            $pageCount = $pdf->setSourceFile($sourcePath);

            for ($page = 1; $page <= $pageCount; $page++) {
                $templateId = $pdf->importPage($page);
                $templateSize = $pdf->getTemplateSize($templateId);

                $widthMm = $this->dimensionToMm((float) ($templateSize['width'] ?? 0));
                $heightMm = $this->dimensionToMm((float) ($templateSize['height'] ?? 0));

                if ($widthMm <= 0 || $heightMm <= 0) {
                    throw PdfNormalizationException::failed('Invalid source page dimensions.');
                }

                $orientation = ($templateSize['orientation'] ?? null) === 'L'
                    ? 'L'
                    : ($widthMm >= $heightMm ? 'L' : 'P');

                $pdf->AddPage($orientation, [$widthMm, $heightMm]);
                $pdf->useTemplate($templateId, 0.0, 0.0, $widthMm, $heightMm);

                $pageLog[] = [
                    'source_index' => $sourceIndex,
                    'page' => $page,
                    'width_mm' => $widthMm,
                    'height_mm' => $heightMm,
                ];
                $totalPages++;
            }
        }

        if ($totalPages === 0) {
            throw PdfNormalizationException::failed('No pages were merged.');
        }

        $this->ensureOutputDirectory($outputAbsolutePath);
        $pdf->Output('F', $outputAbsolutePath);

        return [
            'page_count' => $totalPages,
            'pages' => $pageLog,
        ];
    }

    private function ensureOutputDirectory(string $outputAbsolutePath): void
    {
        $directory = dirname($outputAbsolutePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw PdfNormalizationException::failed('Could not create output directory.');
        }
    }

    /**
     * FPDI reports template sizes in the document unit (mm when configured as mm).
     * Do not treat large mm values (e.g. A4 width ≈ 210) as PDF points.
     */
    private function dimensionToMm(float $value): float
    {
        if ($value <= 0) {
            return 0.0;
        }

        $unit = (string) config('pdf.fpdi.unit', 'mm');

        if ($unit === 'mm') {
            return round($value, 2);
        }

        return round($value * 25.4 / 72, 2);
    }
}
