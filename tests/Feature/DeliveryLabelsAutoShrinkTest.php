<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\Merchant\Printing\DeliveryLabels\CourierAddressTypographyService;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryLabelsAutoShrinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    public function test_delivery_labels_page_renders_auto_shrink_preview_markup(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->get(route('printing.delivery_labels.index'));

        $response->assertOk();
        $response->assertSee('deliveryLabelsWorkspace', false);
        $response->assertSee('data-delivery-label-preview', false);
        $response->assertSee('data-delivery-label-address', false);
        $response->assertSee('data-delivery-label-remarks', false);
        $response->assertSee(__('merchant.delivery_labels.preview.remarks_heading'), false);
        $response->assertSee(__('merchant.delivery_labels.samples.long_title'), false);
    }

    public function test_delivery_label_preview_service_marks_long_addresses_as_shrunk(): void
    {
        $longAddress = (string) __('merchant.delivery_labels.samples.long_address');
        $fontSize = (new CourierAddressTypographyService)->resolveFontSizePx($longAddress);

        $this->assertLessThan(CourierAddressTypographyService::DEFAULT_FONT_SIZE_PX, $fontSize);
        $this->assertGreaterThanOrEqual(CourierAddressTypographyService::MIN_FONT_SIZE_PX, $fontSize);
    }
}
