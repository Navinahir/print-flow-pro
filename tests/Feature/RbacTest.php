<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
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

    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::SuperAdmin->value);

        foreach (PermissionEnum::fromConfig() as $permission) {
            $this->assertTrue($user->can($permission), "Missing permission: {$permission}");
        }
    }

    public function test_merchant_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Merchant->value);

        $this->assertFalse($user->can(PermissionEnum::AccessAdminPanel->value));
        $this->assertFalse($user->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_seeded_super_admin_can_access_admin_panel(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', AdminUserSeeder::ADMIN_EMAIL)->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_seeded_admin_password_is_hashed(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', AdminUserSeeder::ADMIN_EMAIL)->first();

        $this->assertNotSame('password', $admin->password);
        $this->assertTrue(password_verify('password', $admin->password));
    }
}
