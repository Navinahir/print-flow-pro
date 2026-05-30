<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\UploadJob;
use App\Models\User;
use App\Services\Merchant\LocaleService;
use App\Services\Merchant\Preview\PreviewConfigurationService;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UploadPreviewIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    public function test_upload_show_page_renders_preview_engine(): void
    {
        $user = User::factory()->asMerchant()->create();
        Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'email' => $user->email,
        ]);

        $job = UploadJob::factory()->create([
            'merchant_id' => $user->merchant->id,
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('uploads.show', $job));

        $response->assertOk();
        $response->assertSee('data-merchant-preview-root', false);
        $response->assertSee('uploadPreview', false);
        $response->assertSee(__('merchant.uploads.preview.heading'), false);
    }

    public function test_upload_preview_api_returns_payload(): void
    {
        $user = User::factory()->asMerchant()->create();
        Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'email' => $user->email,
        ]);

        $job = UploadJob::factory()->create([
            'merchant_id' => $user->merchant->id,
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('uploads.preview.show', $job));

        $response->assertOk();
        $response->assertJsonStructure(['available', 'preview', 'preview_type']);
    }
}
