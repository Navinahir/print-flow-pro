<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BillingPlanStatus;
use App\Models\BillingPlan;
use Illuminate\Database\Seeder;

class BillingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For individual sellers getting started.',
                'price_cents' => 0,
                'sort_order' => 1,
                'features' => ['pdf_merge' => true, 'picking_list' => true],
                'limits' => ['uploads_per_day' => 50],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing shops with higher volume.',
                'price_cents' => 2900,
                'sort_order' => 2,
                'features' => ['pdf_merge' => true, 'picking_list' => true, 'delivery_labels' => true],
                'limits' => ['uploads_per_day' => 500],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For high-volume operations.',
                'price_cents' => 9900,
                'sort_order' => 3,
                'features' => ['pdf_merge' => true, 'picking_list' => true, 'delivery_labels' => true, 'priority_support' => true],
                'limits' => ['uploads_per_day' => null],
            ],
        ];

        foreach ($plans as $plan) {
            BillingPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    ...$plan,
                    'currency' => 'SGD',
                    'billing_cycle' => 'monthly',
                    'status' => BillingPlanStatus::Active,
                ],
            );
        }
    }
}
