<?php

require __DIR__.'/../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

function analyze(string $path): void
{
    $pdf = new Fpdi;
    $pages = $pdf->setSourceFile($path);
    echo basename($path)." pages={$pages}\n";

    for ($p = 1; $p <= $pages; $p++) {
        $id = $pdf->importPage($p);
        $s = $pdf->getTemplateSize($id);
        echo '  page '.$p.': '.round($s['width'], 2).' x '.round($s['height'], 2).' mm'
            .' orientation='.($s['orientation'] ?? '?')."\n";
    }
}

$files = [
    'C:/Users/Bhargav Gohil/Downloads/new/Shipping Labels_A4_260521a.pdf',
    'C:/Users/Bhargav Gohil/Downloads/new/Shipping Labels_thermal_260521a.pdf',
    'C:/Users/Bhargav Gohil/Downloads/new/Shipping Labels_thermal_260521b.pdf',
];

foreach ($files as $file) {
    if (! file_exists($file)) {
        echo "missing {$file}\n";

        continue;
    }

    analyze($file);
    echo "\n";
}
