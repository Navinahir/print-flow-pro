<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

final class PdfFixtureFactory
{
    public static function putThermalLabel(string $relativePath, float $widthMm = 100.0, float $heightMm = 150.0, string $disk = 'temp'): void
    {
        Storage::disk($disk)->put($relativePath, self::renderPdf($widthMm, $heightMm));
    }

    public static function putA4Label(string $relativePath, string $disk = 'temp'): void
    {
        Storage::disk($disk)->put($relativePath, self::renderPdf(210.0, 297.0));
    }

    public static function putUnsupportedLabel(string $relativePath, string $disk = 'temp'): void
    {
        Storage::disk($disk)->put($relativePath, self::renderPdf(50.0, 50.0));
    }

    public static function createThermalLabelPdf(string $absolutePath, float $widthMm = 100.0, float $heightMm = 150.0): void
    {
        self::writeAbsolute($absolutePath, self::renderPdf($widthMm, $heightMm));
    }

    public static function createA4Pdf(string $absolutePath): void
    {
        self::writeAbsolute($absolutePath, self::renderPdf(210.0, 297.0));
    }

    public static function putMultiPageThermalLabel(string $relativePath, int $pages = 3, string $disk = 'temp'): void
    {
        Storage::disk($disk)->put($relativePath, self::renderMultiPagePdf($pages, 100.0, 150.0));
    }

    public static function createMultiPageThermalPdf(string $absolutePath, int $pages = 3): void
    {
        self::writeAbsolute($absolutePath, self::renderMultiPagePdf($pages, 100.0, 150.0));
    }

    private static function renderMultiPagePdf(int $pages, float $widthMm, float $heightMm): string
    {
        $orientation = $widthMm >= $heightMm ? 'L' : 'P';
        $pdf = new Fpdi($orientation, 'mm');

        for ($page = 1; $page <= max(1, $pages); $page++) {
            $pdf->AddPage($orientation, [$widthMm, $heightMm]);
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->Cell(0, 10, "Fixture PDF page {$page}");
        }

        return $pdf->Output('S');
    }

    private static function renderPdf(float $widthMm, float $heightMm): string
    {
        $orientation = $widthMm >= $heightMm ? 'L' : 'P';
        $pdf = new Fpdi($orientation, 'mm');
        $pdf->AddPage($orientation, [$widthMm, $heightMm]);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(0, 10, 'Fixture PDF');

        return $pdf->Output('S');
    }

    private static function writeAbsolute(string $absolutePath, string $contents): void
    {
        $directory = dirname($absolutePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Could not create PDF fixture directory.');
        }

        file_put_contents($absolutePath, $contents);
    }
}
