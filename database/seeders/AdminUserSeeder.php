<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MerchantStatus;
use App\Enums\Role as RoleEnum;
use App\Models\BillingPlan;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public const ADMIN_DEMO_EMAIL = 'admin@example.com';

    public const MERCHANT_DEMO_EMAIL = 'merchant@example.com';

    public const DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => self::ADMIN_DEMO_EMAIL],
            [
                'name' => 'Admin Staff',
                'password' => self::DEFAULT_PASSWORD,
                'email_verified_at' => now(),
            ],
        );
        $admin->assignPrimaryRole(RoleEnum::Admin);

        $merchant = User::query()->updateOrCreate(
            ['email' => self::MERCHANT_DEMO_EMAIL],
            [
                'name' => 'Demo Merchant',
                'password' => self::DEFAULT_PASSWORD,
                'email_verified_at' => now(),
            ],
        );
        $merchant->assignPrimaryRole(RoleEnum::Merchant);

        $starterPlan = BillingPlan::query()->where('slug', 'starter')->first();

        Merchant::query()->updateOrCreate(
            ['user_id' => $merchant->id],
            [
                'created_by' => $admin->id,
                'billing_plan_id' => $starterPlan?->id,
                'country_code' => 'TW',
                'name' => 'Demo Merchant',
                'shop_name' => 'Demo Shop',
                'phone' => null,
                'status' => MerchantStatus::Active,
                'onboarded_at' => now(),
            ],
        );

        echo '✓ Seeded users (password: '.self::DEFAULT_PASSWORD."):\n";
        echo '  - admin (admin surface only): '.self::ADMIN_DEMO_EMAIL."\n";
        echo '  - merchant (merchant surface only): '.self::MERCHANT_DEMO_EMAIL."\n";
    }
}
