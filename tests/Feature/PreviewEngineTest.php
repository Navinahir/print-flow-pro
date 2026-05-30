<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    public function test_printing_module_renders_preview_engine_markup(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->get(route('printing.order_details.index'));

        $response->assertOk();
        $response->assertSee('data-preview-width-mm="150"', false);
        $response->assertSee('data-preview-height-mm="100"', false);
        $response->assertSee('data-merchant-preview-root', false);
        $response->assertSee('data-preview-container', false);
        $response->assertSee(__('merchant.preview.dimensions_label', ['width' => 150, 'height' => 100]), false);
        $response->assertSee(__('merchant.preview.empty.title'), false);
        $response->assertSee('data-preview-safe-zone', false);
        $response->assertSee('data-safe-zone-inset-mm="5"', false);
        $response->assertSee(__('merchant.preview.safe_zone.toggle_hide'), false);
        $response->assertSee('data-preview-aspect-warning', false);
        $response->assertSee(__('merchant.preview.aspect_ratio.banner_title'), false);
        $response->assertSee(__('merchant.preview.aspect_ratio.force_adjustment'), false);
        $response->assertSee('validateUrl', false);
    }
}
