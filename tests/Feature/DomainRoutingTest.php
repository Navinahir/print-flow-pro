<?php

namespace Tests\Feature;

use App\Services\Domain\DomainConfigurationService;
use Tests\TestCase;

class DomainRoutingTest extends TestCase
{
    public function test_marketing_home_is_available_when_domain_routing_is_disabled(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_admin_obfuscation_blocks_legacy_admin_path_when_routing_enabled(): void
    {
        if (! config('domains.routing_enabled', true)) {
            $this->markTestSkipped('Domain routing is disabled in this environment.');
        }

        $adminDomain = config('domains.admin.domain');

        $this->get('http://'.$adminDomain.'/admin')
            ->assertNotFound();
    }

    public function test_inactive_merchant_region_returns_forbidden_when_routing_enabled(): void
    {
        if (! config('domains.routing_enabled', true)) {
            $this->markTestSkipped('Domain routing is disabled in this environment.');
        }

        $phConfig = app(DomainConfigurationService::class)->configForRegionKey('ph');

        if ($phConfig === null) {
            $this->markTestSkipped('PH merchant domain is not configured.');
        }

        if ($phConfig->isActive) {
            $this->markTestSkipped('PH region is active in this environment.');
        }

        $phDomain = $phConfig->host;

        if (blank($phDomain)) {
            $this->markTestSkipped('PH merchant domain host is not configured.');
        }

        $this->get('http://'.$phDomain.'/')
            ->assertForbidden();
    }
}
