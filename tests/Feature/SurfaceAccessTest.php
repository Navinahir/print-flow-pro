<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurfaceAccessTest extends TestCase
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

    public function test_merchant_can_access_merchant_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignPrimaryRole(RoleEnum::Merchant);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_admin_cannot_access_merchant_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignPrimaryRole(RoleEnum::Admin);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_cannot_log_in_via_merchant_login(): void
    {
        $user = User::factory()->create();
        $user->assignPrimaryRole(RoleEnum::Admin);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_merchant_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignPrimaryRole(RoleEnum::Merchant);

        $this->assertFalse($user->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_admin_can_access_admin_but_not_merchant_surface(): void
    {
        $admin = User::factory()->create();
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $this->assertNotNull($admin);
        $this->assertTrue($admin->canAccessAdminSurface());
        $this->assertFalse($admin->canAccessMerchantSurface());
        $this->assertTrue($admin->canAccessPanel(filament()->getPanel('admin')));
    }
}
