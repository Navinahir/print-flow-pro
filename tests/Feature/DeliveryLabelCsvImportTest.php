<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeliveryLabelCsvImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
        Storage::fake('temp');
    }

    private function createMerchantUser(): User
    {
        $user = User::factory()->asMerchant()->create();

        Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'email' => $user->email,
        ]);

        return $user->fresh(['merchant']);
    }

    public function test_merchant_can_import_delivery_label_csv(): void
    {
        $user = $this->createMerchantUser();

        $csv = "recipient_name,courier_address,remarks,tracking_number,carrier\n";
        $csv .= "Alice Chen,No. 1 Sample Road Taipei,Leave at door,TW123,Black Cat\n";

        $file = UploadedFile::fake()->createWithContent('labels.csv', $csv);

        $response = $this->actingAs($user)->postJson(route('printing.delivery_labels.csv.store'), [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('imported_count', 1);
        $response->assertJsonStructure(['items', 'detected_columns', 'message']);

        $this->assertDatabaseHas('delivery_labels', [
            'recipient_name' => 'Alice Chen',
            'address_line_1' => 'No. 1 Sample Road Taipei',
        ]);
    }

    public function test_csv_import_rejects_missing_columns(): void
    {
        $user = $this->createMerchantUser();

        $csv = "foo,bar\nvalue1,value2\n";
        $file = UploadedFile::fake()->createWithContent('invalid.csv', $csv);

        $response = $this->actingAs($user)->postJson(route('printing.delivery_labels.csv.store'), [
            'file' => $file,
        ]);

        $response->assertUnprocessable();
    }
}
