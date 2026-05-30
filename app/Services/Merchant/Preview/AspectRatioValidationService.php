<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\DTOs\Merchant\Preview\AspectRatioValidationResult;
use App\Support\MerchantConfig;
use Illuminate\Http\UploadedFile;
use Throwable;

class AspectRatioValidationService
{
    public function __construct(
        private readonly PreviewConfigurationService $previewConfiguration,
    ) {}

    public function validateDimensions(int $width, int $height, ?float $tolerancePercent = null): AspectRatioValidationResult
    {
        $tolerance = $tolerancePercent ?? $this->defaultTolerancePercent();
        $targetRatio = $this->targetRatio();

        if ($width <= 0 || $height <= 0) {
            return new AspectRatioValidationResult(
                valid: false,
                deviationPercent: 100.0,
                targetRatio: $targetRatio,
                actualRatio: null,
                width: $width,
                height: $height,
                tolerancePercent: $tolerance,
            );
        }

        $actualRatio = $width / $height;
        $deviationPercent = $this->calculateDeviationPercent($actualRatio);
        $valid = $deviationPercent <= $tolerance;

        return new AspectRatioValidationResult(
            valid: $valid,
            deviationPercent: $deviationPercent,
            targetRatio: $targetRatio,
            actualRatio: $actualRatio,
            width: $width,
            height: $height,
            tolerancePercent: $tolerance,
        );
    }

    public function validateUploadedFile(UploadedFile $file, ?float $tolerancePercent = null): AspectRatioValidationResult
    {
        $dimensions = $this->resolveFileDimensions($file);
        $targetRatio = $this->targetRatio();

        if ($dimensions === null) {
            return new AspectRatioValidationResult(
                valid: true,
                deviationPercent: 0.0,
                targetRatio: $targetRatio,
                actualRatio: null,
                width: null,
                height: null,
                tolerancePercent: $tolerancePercent ?? $this->defaultTolerancePercent(),
            );
        }

        return $this->validateDimensions(
            $dimensions['width'],
            $dimensions['height'],
            $tolerancePercent,
        );
    }

    public function calculateDeviationPercent(float $actualRatio): float
    {
        if ($actualRatio <= 0) {
            return 100.0;
        }

        $targetRatio = $this->targetRatio();

        return abs($actualRatio - $targetRatio) / $targetRatio * 100;
    }

    public function targetRatio(): float
    {
        return $this->previewConfiguration->configuration()->aspectRatio;
    }

    public function targetWidthMm(): float
    {
        return $this->previewConfiguration->configuration()->widthMm;
    }

    public function targetHeightMm(): float
    {
        return $this->previewConfiguration->configuration()->heightMm;
    }

    public function defaultTolerancePercent(): float
    {
        $configured = MerchantConfig::get('preview.aspect_tolerance_percent');

        if (is_numeric($configured)) {
            return (float) $configured;
        }

        return 10.0;
    }

    /**
     * @return array{width: int, height: int}|null
     */
    private function resolveFileDimensions(UploadedFile $file): ?array
    {
        $path = $file->getRealPath();

        if ($path === false) {
            return null;
        }

        try {
            if ($this->isImageMime($file->getMimeType())) {
                $size = @getimagesize($path);

                if ($size !== false && isset($size[0], $size[1])) {
                    return [
                        'width' => (int) $size[0],
                        'height' => (int) $size[1],
                    ];
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function isImageMime(?string $mime): bool
    {
        return is_string($mime) && str_starts_with($mime, 'image/');
    }
}
