<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Pdf;

use App\DTOs\Merchant\Pdf\PdfEngineConfiguration;

/**
 * Resolves PDF engine settings from config/pdf.php and domain preview settings.
 */
interface PdfConfigurationInterface
{
    public function configuration(): PdfEngineConfiguration;
}
