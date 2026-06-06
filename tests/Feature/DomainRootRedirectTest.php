<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DomainSetting;
use App\Services\Domain\DomainConfigurationService;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainRootRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'domains.routing_enabled' => true,
            'domains.port_routing' => false,
        ]);

        $this->seed(DomainSettingSeeder::class);
        app(DomainConfigurationService::class)->forgetCache();
    }

    public function test_merchant_root_shows_unauthorized_for_guests(): void
    {
        $tw = app(DomainConfigurationService::class)->configForRegionKey('tw');
        $this->assertNotNull($tw);

        $this->get('http://'.$tw->host.'/')
            ->assertForbidden()
            ->assertSee(__('merchant.unauthorized.heading'), false);
    }

    public function test_merchant_login_is_reachable_without_auth(): void
    {
        $tw = app(DomainConfigurationService::class)->configForRegionKey('tw');
        $this->assertNotNull($tw);

        $this->get('http://'.$tw->host.'/login')
            ->assertOk();
    }

    public function test_admin_root_shows_unauthorized_for_guests(): void
    {
        $adminHost = app(DomainConfigurationService::class)->effectiveAdminHost();

        $this->get('http://'.$adminHost.'/')
            ->assertForbidden()
            ->assertSee(__('admin.unauthorized.heading'), false);
    }

    public function test_admin_boss_login_is_reachable_without_auth(): void
    {
        $adminHost = app(DomainConfigurationService::class)->effectiveAdminHost();
        $prefix = app(DomainConfigurationService::class)->adminPathPrefix();

        $this->get('http://'.$adminHost.'/'.$prefix.'/login')
            ->assertOk();
    }

    public function test_marketing_root_redirects_to_locale_path(): void
    {
        $marketingHost = app(DomainConfigurationService::class)->effectiveMarketingHost();

        $this->get('http://'.$marketingHost.'/')
            ->assertRedirect('/tw');
    }

    public function test_infrastructure_hosts_load_from_database_after_seed(): void
    {
        $service = app(DomainConfigurationService::class);

        $marketing = DomainSetting::query()
            ->where('region_key', 'marketing')
            ->first();
        $admin = DomainSetting::query()
            ->where('region_key', 'admin')
            ->first();

        $this->assertNotNull($marketing);
        $this->assertNotNull($admin);
        $this->assertSame($marketing->host, $service->effectiveMarketingHost());
        $this->assertSame($admin->host, $service->effectiveAdminHost());
        $this->assertSame('boss', $service->adminPathPrefix());
    }
}
