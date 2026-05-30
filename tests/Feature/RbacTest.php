<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
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

    public function test_admin_has_expected_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignPrimaryRole(RoleEnum::Admin);

        $this->assertTrue($user->can(PermissionEnum::AccessAdminPanel->value));
        $this->assertTrue($user->can(PermissionEnum::ViewMerchants->value));
        $this->assertFalse($user->can(PermissionEnum::ManageRoles->value));
    }

    public function test_merchant_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignPrimaryRole(RoleEnum::Merchant);

        $this->assertFalse($user->can(PermissionEnum::AccessAdminPanel->value));
        $this->assertFalse($user->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create();
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $this->assertTrue($admin->canAccessPanel(filament()->getPanel('admin')));
    }
}
