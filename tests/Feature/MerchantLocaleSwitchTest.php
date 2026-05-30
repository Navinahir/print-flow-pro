<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\Merchant\LocaleService;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantLocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    public function test_merchant_can_switch_locale(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->post(route('locale.update'), [
            'locale' => 'en',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas(LocaleService::SESSION_KEY, 'en');
        $this->assertSame('en', app()->getLocale());
    }

    public function test_guest_can_switch_locale(): void
    {
        $response = $this->post(route('locale.update'), [
            'locale' => 'en',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas(LocaleService::SESSION_KEY, 'en');
        $response->assertCookie(LocaleService::COOKIE_KEY, 'en');
    }

    public function test_guest_login_page_renders_auth_navbar(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('merchant-guest-header', false);
        $response->assertSee('merchant-brand-mark', false);
        $response->assertSee(__('merchant.locale.switcher_label'), false);
        $response->assertSee(__('merchant.theme.switcher_label'), false);
        $response->assertSee('data-current-locale', false);
        $response->assertSee('data-locale-url', false);
    }

    public function test_merchant_header_renders_locale_switcher(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('merchant.locale.switcher_label'), false);
        $response->assertSee(__('merchant.theme.switcher_label'), false);
        $response->assertSee(__('merchant.components.page_loader.content_message'), false);
        $response->assertSee('merchant-page-loading-pending', false);
    }
}
