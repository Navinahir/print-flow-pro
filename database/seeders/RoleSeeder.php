<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('permissions.guard', 'web');
        $rolePermissions = config('permissions.role_permissions', []);

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, $guard);

            if ($permissions === 'all') {
                $role->syncPermissions(PermissionEnum::fromConfig());

                continue;
            }

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
