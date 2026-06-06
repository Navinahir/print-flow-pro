<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfProcessorInterface;
use App\Enums\PdfProcessingMode;

/**
 * Resolves the module processor for a given PDF processing mode.
 */
class PdfProcessorRegistry
{
    /**
     * @param  iterable<PdfProcessorInterface>  $processors
     */
    public function __construct(
        private readonly iterable $processors,
    ) {}

    public function resolve(PdfProcessingMode $mode): ?PdfProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($mode)) {
                return $processor;
            }
        }

        $configured = config('pdf.modes.'.$mode->configKey().'.processor');

        if (is_string($configured) && class_exists($configured)) {
            $instance = app($configured);

            if ($instance instanceof PdfProcessorInterface && $instance->supports($mode)) {
                return $instance;
            }
        }

        return null;
    }
}
