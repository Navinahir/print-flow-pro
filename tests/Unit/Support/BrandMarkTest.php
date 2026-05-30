<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\BrandMark;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BrandMarkTest extends TestCase
{
    #[DataProvider('initialsProvider')]
    public function test_initials(string $brandName, string $expected): void
    {
        $this->assertSame($expected, BrandMark::initials($brandName));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function initialsProvider(): array
    {
        return [
            'short first word' => ['XY Cubic Shopee', 'XY'],
            'single word' => ['Acme', 'AC'],
            'two words' => ['Demo Merchant', 'DM'],
        ];
    }
}
