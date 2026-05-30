<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintingModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
    }

    public function test_merchant_can_access_order_details_printing_module(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->get(route('printing.order_details.index'));

        $response->assertOk();
        $response->assertSee(__('merchant.printing.modules.order_details.title'), false);
    }

    public function test_merchant_can_access_all_printing_module_routes(): void
    {
        $user = User::factory()->asMerchant()->create();

        $routes = [
            'printing.order_details.index',
            'printing.logistics_labels.index',
            'printing.picking_list.index',
            'printing.delivery_labels.index',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_guest_cannot_access_printing_modules(): void
    {
        $this->get(route('printing.order_details.index'))
            ->assertRedirect(route('login'));
    }
}
