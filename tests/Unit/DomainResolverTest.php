<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Domain\DomainConfigurationService;
use App\Support\Domains\DomainContext;
use App\Support\Domains\DomainResolver;
use Illuminate\Http\Request;
use Tests\TestCase;

class DomainResolverTest extends TestCase
{
    public function test_https_request_matches_merchant_host_without_port_suffix(): void
    {
        config([
            'domains.routing_enabled' => true,
            'domains.port_routing' => false,
            'domains.fallback_infrastructure' => [
                'marketing' => ['host' => 'xycubic.com', 'session_cookie' => 'test-marketing'],
                'admin' => [
                    'host' => 'manage-xy.xycubic.com',
                    'session_cookie' => 'test-admin',
                    'path_prefix' => 'boss',
                ],
            ],
        ]);

        app(DomainConfigurationService::class)->forgetCache();

        $resolver = app(DomainResolver::class);

        $merchant = $resolver->resolve(Request::create('https://tw.xycubic.com/login', 'GET'));
        $this->assertSame(DomainContext::SURFACE_MERCHANT, $merchant->surface);
        $this->assertSame('tw', $merchant->regionKey);

        $admin = $resolver->resolve(Request::create('https://manage-xy.xycubic.com/', 'GET'));
        $this->assertSame(DomainContext::SURFACE_ADMIN, $admin->surface);

        $marketing = $resolver->resolve(Request::create('https://xycubic.com/', 'GET'));
        $this->assertSame(DomainContext::SURFACE_MARKETING, $marketing->surface);
    }

    public function test_port_routing_only_applies_to_loopback_hosts(): void
    {
        config(['domains.port_routing' => true]);

        $resolver = app(DomainResolver::class);

        $this->assertTrue($resolver->usesPortBasedLocalHost('localhost:8000'));
        $this->assertFalse($resolver->usesPortBasedLocalHost('tw.xycubic.com'));
    }
}
