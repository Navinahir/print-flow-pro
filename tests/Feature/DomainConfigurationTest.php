<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DomainSetting;
use App\Services\Domain\DomainConfigurationService;
use App\Support\MerchantConfig;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_infrastructure_domains_load_from_database_after_seed(): void
    {
        $this->seed(DomainSettingSeeder::class);

        $service = app(DomainConfigurationService::class);
        $service->forgetCache();

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
    }

    public function test_merchant_domain_settings_load_from_database_after_seed(): void
    {
        $this->seed(DomainSettingSeeder::class);

        $service = app(DomainConfigurationService::class);
        $service->forgetCache();

        $tw = $service->configForRegionKey('tw');

        $this->assertNotNull($tw);
        $this->assertSame('tw', $tw->regionKey);
        $this->assertTrue($tw->isActive);
        $this->assertTrue($tw->isFeatureEnabled('uploads'));
        $this->assertTrue($tw->isFeatureEnabled('printing_order_details'));
        $this->assertSame('zh-TW', $tw->defaultLocale);
    }

    public function test_merchant_config_helper_reads_branding_from_current_context(): void
    {
        $this->seed(DomainSettingSeeder::class);

        $service = app(DomainConfigurationService::class);
        $service->forgetCache();

        $tw = $service->configForRegionKey('tw');
        $this->assertNotNull($tw);

        $response = $this->get('http://'.$tw->host.'/login');

        $response->assertOk();
        $this->assertSame($tw->brandName, MerchantConfig::get('brand.name'));
    }
}
