<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Domain\DomainConfigurationService;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingPathIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! config('domains.routing_enabled', true)) {
            $this->markTestSkipped('Domain routing is disabled in this environment.');
        }

        $this->seed(DomainSettingSeeder::class);
        app(DomainConfigurationService::class)->forgetCache();
    }

    public function test_merchant_host_redirects_marketing_locale_path_to_login(): void
    {
        $tw = app(DomainConfigurationService::class)->configForRegionKey('tw');
        $this->assertNotNull($tw);

        $this->get('http://'.$tw->host.'/tw')
            ->assertRedirect(route('login', absolute: false));
    }

    public function test_merchant_login_does_not_set_marketing_locale_cookie(): void
    {
        $tw = app(DomainConfigurationService::class)->configForRegionKey('tw');
        $this->assertNotNull($tw);

        $response = $this->get('http://'.$tw->host.'/login');

        $response->assertOk();
        $response->assertCookieMissing('xycubic-marketing-locale');
    }
}
