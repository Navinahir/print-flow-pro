<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('permissions.guard', 'web');

        foreach (config('permissions.groups', []) as $permissions) {
            foreach ($permissions as $name) {
                Permission::findOrCreate($name, $guard);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
