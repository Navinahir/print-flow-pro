<?php

require __DIR__.'/../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

function pageSize(string $path): array
{
    $pdf = new Fpdi;
    $pdf->setSourceFile($path);
    $size = $pdf->getTemplateSize($pdf->importPage(1));

    return [round((float) $size['width'], 2), round((float) $size['height'], 2)];
}

$compare = [
    'reference' => 'C:/Users/Bhargav Gohil/Downloads/new/Shipping Labels_A4_260521a.pdf',
    'ours' => storage_path('app/temp/client-verify/multi-a4-output.pdf'),
    'slot' => storage_path('app/temp/client-verify/multi-p1-slot.pdf'),
];

foreach ($compare as $label => $path) {
    if (! file_exists($path)) {
        echo "{$label}: missing\n";

        continue;
    }

    [$w, $h] = pageSize($path);
    echo "{$label}: {$w} x {$h} mm\n";
}
