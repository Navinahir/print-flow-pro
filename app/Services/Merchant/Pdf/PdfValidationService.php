<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf;

use App\Contracts\Merchant\Pdf\PdfConfigurationInterface;
use App\Contracts\Merchant\Pdf\PdfValidationInterface;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfTempPath;
use App\DTOs\Merchant\Pdf\PdfValidationResult;
use App\Enums\PdfProcessingMode;
use App\Enums\PdfValidationCode;
use App\Exceptions\Merchant\Pdf\PdfBoundaryDetectionException;
use App\Exceptions\Merchant\Pdf\PdfValidationException;

/**
 * Framework-level validation with thermal label rules delegated to ThermalPdfValidationService.
 */
class PdfValidationService implements PdfValidationInterface
{
    public function __construct(
        private readonly PdfConfigurationInterface $configurationService,
        private readonly PdfBoundaryDetectionService $boundaryDetectionService,
        private readonly PdfTempStorageService $tempStorageService,
    ) {}

    public function validateContext(PdfProcessingContext $context): PdfValidationResult
    {
        $modeConfig = config('pdf.modes.'.$context->mode->configKey());

        if (! is_array($modeConfig) || ! ($modeConfig['enabled'] ?? false)) {
            return PdfValidationResult::failed(PdfValidationCode::UnsupportedMode);
        }

        if ($context->sourceRelativePaths === []) {
            return PdfValidationResult::failed(PdfValidationCode::MissingSource);
        }

        $errors = [];

        foreach ($context->sourceRelativePaths as $relativePath) {
            $absolutePath = $this->tempStorageService->absolutePath(
                new PdfTempPath(
                    disk: (string) config('pdf.temp_disk', 'temp'),
                    relativePath: $relativePath,
                ),
            );

            $result = $this->validateSourceFile($absolutePath, $context->mode);

            if (! $result->passed) {
                $errors = array_merge($errors, $result->messages);
            }
        }

        if ($errors !== []) {
            return new PdfValidationResult(false, [PdfValidationCode::MissingSource], $errors);
        }

        return PdfValidationResult::valid();
    }

    public function validateSourceFile(string $absolutePath, PdfProcessingMode $mode): PdfValidationResult
    {
        $configuration = $this->configurationService->configuration();

        if (! is_readable($absolutePath)) {
            return PdfValidationResult::failed(PdfValidationCode::FileNotReadable);
        }

        $sizeBytes = filesize($absolutePath);

        if ($sizeBytes !== false && $sizeBytes > $configuration->maxSourceBytes) {
            return PdfValidationResult::failed(PdfValidationCode::FileTooLarge);
        }

        if (! $this->isPdfPath($absolutePath)) {
            // Spreadsheets and HTML exports are validated in module processors (picking/delivery).
            if (in_array($mode, [PdfProcessingMode::PickingListExport], true)) {
                return PdfValidationResult::valid();
            }

            if (in_array($mode, [PdfProcessingMode::DeliveryLabel], true) && $this->isSpreadsheetPath($absolutePath)) {
                return PdfValidationResult::valid();
            }

            return PdfValidationResult::valid();
        }

        try {
            $pageCount = $this->boundaryDetectionService->pageCount($absolutePath);

            if ($pageCount > $configuration->maxPagesPerJob) {
                return PdfValidationResult::failed(PdfValidationCode::PageLimitExceeded);
            }

            if ($mode === PdfProcessingMode::ThermalLabel) {
                return $this->validateThermalPdfPages($absolutePath, $pageCount);
            }
        } catch (PdfBoundaryDetectionException) {
            return PdfValidationResult::failed(PdfValidationCode::InvalidPdf);
        }

        return PdfValidationResult::valid();
    }

    /**
     * @throws PdfValidationException
     */
    public function assertValid(PdfProcessingContext $context): PdfValidationResult
    {
        $result = $this->validateContext($context);

        if (! $result->passed) {
            throw new PdfValidationException(
                $result->codes[0] ?? PdfValidationCode::MissingSource,
                $result->messages[0] ?? null,
            );
        }

        return $result;
    }

    /**
     * Validates every page in a thermal PDF (A4 reject + supported size range).
     */
    private function validateThermalPdfPages(string $absolutePath, int $pageCount): PdfValidationResult
    {
        $thermalValidator = app(ThermalPdfValidationService::class);

        for ($page = 1; $page <= $pageCount; $page++) {
            $boundary = $this->boundaryDetectionService->detectFromFile($absolutePath, $page);
            $result = $thermalValidator->validateBoundary($boundary);

            if (! $result->passed) {
                return $result;
            }
        }

        return PdfValidationResult::valid();
    }

    private function isPdfPath(string $path): bool
    {
        return str_ends_with(strtolower($path), '.pdf');
    }

    private function isSpreadsheetPath(string $path): bool
    {
        return (bool) preg_match('/\.(csv|xlsx|xls)$/i', $path);
    }
}
