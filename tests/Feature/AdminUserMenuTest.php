<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use App\Services\Domain\DomainConfigurationService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }

    public function test_logout_menu_item_is_grouped_directly_below_profile(): void
    {
        $admin = User::factory()->create();
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $this->actingAs($admin);

        $items = filament()->getUserMenuItems();

        $this->assertLessThan(0, $items['profile']->getSort());
        $this->assertLessThan(0, $items['logout']->getSort());
        $this->assertLessThan($items['logout']->getSort(), $items['profile']->getSort());
    }

    public function test_profile_menu_item_links_to_profile_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $this->actingAs($admin);

        $profileAction = collect(filament()->getUserMenuItems())->get('profile');

        $this->assertNotNull($profileAction);
        $this->assertSame(
            filament()->getProfileUrl(),
            $profileAction->getUrl(),
        );
    }

    public function test_admin_can_access_profile_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $adminHost = app(DomainConfigurationService::class)->effectiveAdminHost();
        $prefix = app(DomainConfigurationService::class)->adminPathPrefix();

        $this->actingAs($admin)
            ->get("http://{$adminHost}/{$prefix}/profile")
            ->assertOk()
            ->assertSee(__('admin.nav.profile'), false)
            ->assertSee(__('admin.profile.password.title'), false)
            ->assertSee(__('admin.profile.password.current'), false);
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create();
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $adminHost = app(DomainConfigurationService::class)->effectiveAdminHost();
        $prefix = app(DomainConfigurationService::class)->adminPathPrefix();

        $this->actingAs($admin)
            ->post("http://{$adminHost}/{$prefix}/logout")
            ->assertRedirect("http://{$adminHost}/{$prefix}/login");

        $this->assertGuest();
    }

    public function test_login_page_uses_login_brand_name(): void
    {
        $adminHost = app(DomainConfigurationService::class)->effectiveAdminHost();
        $prefix = app(DomainConfigurationService::class)->adminPathPrefix();

        $this->get("http://{$adminHost}/{$prefix}/login")
            ->assertOk()
            ->assertSee('XYCubic Admin Portal', false);
    }

    public function test_dashboard_uses_sidebar_brand_name(): void
    {
        $admin = User::factory()->create();
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $adminHost = app(DomainConfigurationService::class)->effectiveAdminHost();
        $prefix = app(DomainConfigurationService::class)->adminPathPrefix();

        $this->actingAs($admin)
            ->get("http://{$adminHost}/{$prefix}")
            ->assertOk()
            ->assertSee('XYCubic Admin', false)
            ->assertDontSee('XYCubic Admin Portal', false);
    }

    public function test_sidebar_includes_account_section_with_profile_and_logout(): void
    {
        $admin = User::factory()->create();
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $adminHost = app(DomainConfigurationService::class)->effectiveAdminHost();
        $prefix = app(DomainConfigurationService::class)->adminPathPrefix();

        $this->actingAs($admin)
            ->get("http://{$adminHost}/{$prefix}")
            ->assertOk()
            ->assertSee(__('admin.nav.account'), false)
            ->assertSee(__('admin.nav.profile'), false)
            ->assertSee(__('admin.nav.logout'), false)
            ->assertSee('fi-admin-sidebar-account', false)
            ->assertSee(filament()->getProfileUrl(), false)
            ->assertSee(filament()->getLogoutUrl(), false);
    }
}
