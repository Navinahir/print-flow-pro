<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintingPreviewContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    public function test_order_details_renders_live_preview_content(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->get(route('printing.order_details.index'));

        $response->assertOk();
        $response->assertSee('data-printing-preview-content', false);
        $response->assertSee('printing-preview-order-details', false);
        $response->assertSee(__('merchant.printing.preview.order_details.fields.total'), false);
    }

    public function test_logistics_labels_renders_barcode_preview(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->get(route('printing.logistics_labels.index'));

        $response->assertOk();
        $response->assertSee('printing-preview-logistics', false);
        $response->assertSee(__('merchant.printing.preview.logistics_labels.fields.tracking'), false);
    }

    public function test_picking_list_renders_table_preview(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->get(route('printing.picking_list.index'));

        $response->assertOk();
        $response->assertSee('printing-preview-picking-list', false);
        $response->assertSee(__('merchant.printing.preview.picking_list.fields.product_name'), false);
    }

    public function test_preview_api_returns_order_details_payload(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->postJson(route('printing.preview.show'), [
            'module' => 'order_details',
            'item_id' => 'order-sample-1',
        ]);

        $response->assertOk();
        $response->assertJsonPath('preview.type', 'order_details');
        $response->assertJsonStructure(['preview' => ['order_number', 'line_items', 'summary']]);
    }
}
