<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Preview;

final readonly class AspectRatioValidationResult
{
    public function __construct(
        public bool $valid,
        public float $deviationPercent,
        public float $targetRatio,
        public ?float $actualRatio,
        public ?int $width,
        public ?int $height,
        public float $tolerancePercent,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'deviation_percent' => round($this->deviationPercent, 2),
            'target_ratio' => round($this->targetRatio, 4),
            'actual_ratio' => $this->actualRatio !== null ? round($this->actualRatio, 4) : null,
            'width' => $this->width,
            'height' => $this->height,
            'tolerance_percent' => $this->tolerancePercent,
            'target_width_mm' => 150,
            'target_height_mm' => 100,
        ];
    }
}
