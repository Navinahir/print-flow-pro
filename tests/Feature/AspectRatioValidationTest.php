<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AspectRatioValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    public function test_merchant_can_validate_dimensions_via_ajax(): void
    {
        $user = User::factory()->asMerchant()->create();

        $validResponse = $this->actingAs($user)->postJson(route('printing.aspect_ratio.validate'), [
            'width' => 1000,
            'height' => 1500,
        ]);

        $validResponse->assertOk();
        $validResponse->assertJsonPath('valid', true);
        $validResponse->assertJsonPath('target_ratio', round(100 / 150, 4));

        $invalidResponse = $this->actingAs($user)->postJson(route('printing.aspect_ratio.validate'), [
            'width' => 800,
            'height' => 600,
        ]);

        $invalidResponse->assertOk();
        $invalidResponse->assertJsonPath('valid', false);
        $invalidResponse->assertJsonStructure(['message', 'deviation_percent']);
    }

    public function test_guest_cannot_validate_aspect_ratio(): void
    {
        $response = $this->postJson(route('printing.aspect_ratio.validate'), [
            'width' => 1500,
            'height' => 1000,
        ]);

        $response->assertUnauthorized();
    }

    public function test_validation_requires_dimensions_or_file(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->postJson(route('printing.aspect_ratio.validate'), []);

        $response->assertUnprocessable();
    }
}
