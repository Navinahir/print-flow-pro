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
use App\Support\Domains\DomainContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryCodeScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_surface_only_sees_matching_country_upload_jobs(): void
    {
        config([
            'domains.routing_enabled' => true,
            'domains.current.country_code' => 'TW',
        ]);

        app()->instance(DomainContext::class, new DomainContext(
            surface: DomainContext::SURFACE_MERCHANT,
            regionKey: 'tw',
            countryCode: 'TW',
            locale: 'zh-TW',
            host: 'tw.xycubic.com',
            domain: 'tw.xycubic.com',
            active: true,
        ));

        $userTw = User::factory()->create(['role' => RoleEnum::Merchant]);
        $merchantTw = Merchant::query()->create([
            'user_id' => $userTw->id,
            'country_code' => 'TW',
            'name' => 'TW Merchant',
            'status' => MerchantStatus::Active,
        ]);

        $userPh = User::factory()->create(['role' => RoleEnum::Merchant]);
        $merchantPh = Merchant::query()->create([
            'user_id' => $userPh->id,
            'country_code' => 'PH',
            'name' => 'PH Merchant',
            'status' => MerchantStatus::Active,
        ]);

        $twJob = UploadJob::query()->create([
            'merchant_id' => $merchantTw->id,
            'country_code' => 'TW',
            'user_id' => $userTw->id,
            'uploaded_by' => $userTw->id,
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::Pending,
            'file_count' => 1,
        ]);

        UploadJob::query()->create([
            'merchant_id' => $merchantPh->id,
            'country_code' => 'PH',
            'user_id' => $userPh->id,
            'uploaded_by' => $userPh->id,
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::Pending,
            'file_count' => 1,
        ]);

        $this->assertSame([$twJob->id], UploadJob::query()->pluck('id')->all());
    }

    public function test_admin_surface_bypasses_country_scope(): void
    {
        config(['domains.routing_enabled' => true]);

        app()->instance(DomainContext::class, new DomainContext(
            surface: DomainContext::SURFACE_ADMIN,
            regionKey: 'admin',
            countryCode: '--',
            locale: 'en',
            host: 'manage-xy.xycubic.com',
            domain: 'manage-xy.xycubic.com',
            active: true,
        ));

        $userTw = User::factory()->create();
        $userPh = User::factory()->create();

        $merchantTw = Merchant::query()->create([
            'user_id' => $userTw->id,
            'country_code' => 'TW',
            'name' => 'TW Merchant',
            'status' => MerchantStatus::Active,
        ]);

        $merchantPh = Merchant::query()->create([
            'user_id' => $userPh->id,
            'country_code' => 'PH',
            'name' => 'PH Merchant',
            'status' => MerchantStatus::Active,
        ]);

        UploadJob::query()->create([
            'merchant_id' => $merchantTw->id,
            'country_code' => 'TW',
            'user_id' => $userTw->id,
            'uploaded_by' => $userTw->id,
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::Pending,
            'file_count' => 1,
        ]);

        UploadJob::query()->create([
            'merchant_id' => $merchantPh->id,
            'country_code' => 'PH',
            'user_id' => $userPh->id,
            'uploaded_by' => $userPh->id,
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::Pending,
            'file_count' => 1,
        ]);

        $this->assertSame(2, UploadJob::query()->count());
    }
}
