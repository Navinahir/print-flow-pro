<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Merchant\Preview\PreviewConfigurationService;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    public function test_preview_configuration_loads_from_domain_settings(): void
    {
        $configuration = app(PreviewConfigurationService::class)->configuration();

        $this->assertSame(100.0, $configuration->widthMm);
        $this->assertSame(150.0, $configuration->heightMm);
        $this->assertEqualsWithDelta(100 / 150, $configuration->aspectRatio, 0.0001);
        $this->assertSame(5.0, $configuration->safeZoneInsetMm);
        $this->assertSame('fit', $configuration->scalingBehavior);
    }
}
