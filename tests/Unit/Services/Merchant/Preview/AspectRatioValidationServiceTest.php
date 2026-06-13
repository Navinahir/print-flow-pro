<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Preview;

use App\Services\Merchant\Preview\AspectRatioValidationService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AspectRatioValidationServiceTest extends TestCase
{
    private AspectRatioValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AspectRatioValidationService::class);
    }

    #[DataProvider('dimensionProvider')]
    public function test_validate_dimensions(int $width, int $height, bool $expectedValid): void
    {
        $result = $this->service->validateDimensions($width, $height);

        $this->assertSame($expectedValid, $result->valid);
        $this->assertSame($this->service->targetRatio(), $result->targetRatio);
        $this->assertSame($width, $result->width);
        $this->assertSame($height, $result->height);
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: bool}>
     */
    public static function dimensionProvider(): array
    {
        return [
            'exact 2:3 portrait ratio' => [1000, 1500, true],
            'within tolerance' => [1020, 1500, true],
            'exceeds tolerance' => [800, 600, false],
            'invalid zero width' => [0, 1000, false],
        ];
    }

    public function test_calculate_deviation_percent_for_invalid_ratio(): void
    {
        $deviation = $this->service->calculateDeviationPercent(800 / 600);

        $this->assertGreaterThan(10.0, $deviation);
    }

    public function test_calculate_deviation_percent_for_exact_ratio(): void
    {
        $deviation = $this->service->calculateDeviationPercent(100 / 150);

        $this->assertSame(0.0, $deviation);
    }
}
