<?php

declare(strict_types=1);

/**
 * Ensures the global FPDF class is available for setasign/fpdi.
 * Required because FPDI extends FPDF via class inheritance (not PSR-4).
 */
if (! class_exists('FPDF', false)) {
    require_once dirname(__DIR__, 2).'/vendor/setasign/fpdf/fpdf.php';
}
