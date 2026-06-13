<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\MarketingUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingUrlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider localePathProvider
     */
    public function test_home_url_uses_marketing_host_and_locale_path(string $locale, string $expectedPath): void
    {
        config([
            'domains.fallback_infrastructure.marketing.host' => 'localhost:8000',
        ]);

        app()->setLocale($locale);

        $this->assertSame("http://localhost:8000{$expectedPath}", MarketingUrl::home());
    }

  /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function localePathProvider(): array
    {
        return [
            'english' => ['en', '/en'],
            'traditional chinese hyphen' => ['zh-TW', '/tw'],
            'traditional chinese underscore' => ['zh_TW', '/tw'],
        ];
    }
}
