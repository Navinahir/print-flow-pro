<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfConfigurationInterface;
use App\DTOs\Merchant\Pdf\PdfEngineConfiguration;

class ResolvePdfEngineConfiguration
{
    public function __construct(
        private readonly PdfConfigurationInterface $configurationService,
    ) {}

    public function execute(): PdfEngineConfiguration
    {
        return $this->configurationService->configuration();
    }
}
