<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@example.com';

    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles([RoleEnum::SuperAdmin->value]);
    }
}
