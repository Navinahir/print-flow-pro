<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\MerchantStatus;
use App\Enums\Role as RoleEnum;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\Merchant;
use App\Models\UploadJob;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_dashboard_shows_live_upload_stats(): void
    {
        $user = User::factory()->create(['role' => RoleEnum::Merchant]);
        $user->syncRoles([RoleEnum::Merchant->value]);

        $merchant = Merchant::query()->create([
            'user_id' => $user->id,
            'country_code' => 'TW',
            'name' => 'Demo',
            'status' => MerchantStatus::Active,
        ]);

        UploadJob::query()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::Pending,
            'file_count' => 1,
        ]);

        UploadJob::query()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::PickingList,
            'status' => UploadStatus::Completed,
            'file_count' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('2', false);
        $response->assertSee(__('merchant.dashboard.stats.total_uploads'), false);
        $response->assertSee(__('merchant.dashboard.stats.pending_jobs'), false);
    }
}
