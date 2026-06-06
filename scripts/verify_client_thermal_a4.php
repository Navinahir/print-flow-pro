<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingMode;
use App\Services\Merchant\Pdf\PdfBoundaryDetectionService;
use App\Services\Merchant\Pdf\ThermalA4SheetComposer;
use App\Services\Merchant\Pdf\ThermalPdfNormalizationService;
use App\Services\Merchant\Pdf\ThermalPdfValidationService;
use setasign\Fpdi\Fpdi;

$files = [
    'single' => 'C:/Users/Bhargav Gohil/Downloads/new/Shipping Labels_thermal_260521b.pdf',
    'multi' => 'C:/Users/Bhargav Gohil/Downloads/new/Shipping Labels_thermal_260521a.pdf',
];

$validator = app(ThermalPdfValidationService::class);
$normalizer = app(ThermalPdfNormalizationService::class);
$composer = app(ThermalA4SheetComposer::class);
$boundaryDetection = app(PdfBoundaryDetectionService::class);
$context = new PdfProcessingContext(1, 1, 'TW', PdfProcessingMode::ThermalLabel);
$outDir = storage_path('app/temp/client-verify');

if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

foreach ($files as $name => $sourcePath) {
    if (! file_exists($sourcePath)) {
        echo "Missing: {$sourcePath}\n";
        continue;
    }

    echo "=== {$name} ===\n";
    $pageCount = $boundaryDetection->pageCount($sourcePath);
    echo "Source pages: {$pageCount}\n";

    $slots = [];
    for ($page = 1; $page <= $pageCount; $page++) {
        $boundary = $boundaryDetection->detectFromFile($sourcePath, $page);
        $validation = $validator->validateBoundary($boundary);
        if (! $validation->passed) {
            echo "Validation failed page {$page}\n";
            continue 2;
        }

        $slotPath = "{$outDir}/{$name}-p{$page}-slot.pdf";
        $normalizer->renderLabelSlot($sourcePath, $page, $context, $slotPath);
        $slots[] = $slotPath;
    }

    $outputPath = "{$outDir}/{$name}-a4-output.pdf";
    if (count($slots) === 1) {
        $composer->composeSingle($slots[0], $outputPath);
    } else {
        $composer->composeMulti($slots, $outputPath);
    }

    $pdf = new Fpdi;
    $pdf->setSourceFile($outputPath);
    $size = $pdf->getTemplateSize($pdf->importPage(1));
    echo 'Output: '.round($size['width'], 1).' x '.round($size['height'], 1)." mm\n";
    echo "Written: {$outputPath}\n\n";
}
