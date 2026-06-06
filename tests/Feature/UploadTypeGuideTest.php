<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UploadTypeGuideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    private function merchantUser(): User
    {
        $user = User::factory()->asMerchant()->create();
        Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        return $user->fresh();
    }

    public function test_upload_create_page_shows_thermal_label_guide_and_samples(): void
    {
        $response = $this->actingAs($this->merchantUser())->get(route('uploads.create'));

        $response->assertOk();
        $response->assertSee(__('merchant.uploads.guides.heading'), false);
        $response->assertSee('samples/thermal-labels/sample-single.pdf', false);
        $response->assertSee('samples/thermal-labels/sample-multipage.pdf', false);
        $response->assertSee(__('merchant.uploads.guides.heading'), false);
        $response->assertSee('merchant-upload-create__layout', false);
        $response->assertSee('merchant-upload-create__aside', false);
    }

    public function test_upload_create_page_shows_locale_specific_delivery_sample(): void
    {
        $response = $this->actingAs($this->merchantUser())->get(route('uploads.create'));

        $response->assertOk();
        $response->assertSee('samples/delivery-labels/sample-en.csv', false);
        $response->assertSee('samples/delivery-labels/sample-address.xlsx', false);
    }
}
