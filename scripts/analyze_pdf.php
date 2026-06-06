<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../app/Support/pdf_fpdf_bootstrap.php';

use setasign\Fpdi\Fpdi;

$files = array_slice($argv, 1);

foreach ($files as $file) {
    if (! file_exists($file)) {
        echo "Missing: {$file}\n";
        continue;
    }

    $pdf = new Fpdi;
    $pageCount = $pdf->setSourceFile($file);
    echo basename($file)." pages: {$pageCount}\n";

    for ($page = 1; $page <= $pageCount; $page++) {
        $templateId = $pdf->importPage($page);
        $size = $pdf->getTemplateSize($templateId);
        $w = round((float) ($size['width'] ?? 0), 2);
        $h = round((float) ($size['height'] ?? 0), 2);
        $o = $size['orientation'] ?? '';
        echo "  page {$page}: {$w} x {$h} {$o}\n";
    }
}
