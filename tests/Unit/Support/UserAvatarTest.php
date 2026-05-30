<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\UserAvatar;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserAvatarTest extends TestCase
{
    #[DataProvider('initialsProvider')]
    public function test_initials(string $name, string $expected): void
    {
        $this->assertSame($expected, UserAvatar::initials($name));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function initialsProvider(): array
    {
        return [
            'two words' => ['Demo Merchant', 'DM'],
            'single word' => ['Alice', 'AL'],
            'single character word' => ['A', 'A'],
            'extra spaces' => ['  Demo   Merchant  ', 'DM'],
            'empty string' => ['', '?'],
        ];
    }
}
