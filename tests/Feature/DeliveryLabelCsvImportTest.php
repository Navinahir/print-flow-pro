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

    public function test_merchant_can_import_chinese_header_delivery_label_csv(): void
    {
        $user = $this->createMerchantUser();

        $csv = "\xEF\xBB\xBF收件人,地址,備註,追蹤號碼,物流商\n";
        $csv .= '"劉夏莉, 0928000888","806高雄市前鎮區三多三路139號19樓-11","出貨 # 2605211CBCW3DS","TW260971062481V","超商取件"'."\n";

        $file = UploadedFile::fake()->createWithContent('labels-zh.csv', $csv);

        $response = $this->actingAs($user)->postJson(route('printing.delivery_labels.csv.store'), [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('imported_count', 1);

        $this->assertDatabaseHas('delivery_labels', [
            'recipient_name' => '劉夏莉, 0928000888',
            'address_line_1' => '806高雄市前鎮區三多三路139號19樓-11',
        ]);
    }

    public function test_delivery_labels_page_shows_locale_specific_sample_download(): void
    {
        $user = $this->createMerchantUser();

        $this->actingAs($user)
            ->get(route('printing.delivery_labels.index'))
            ->assertOk()
            ->assertSee('samples/delivery-labels/sample-en.csv', false)
            ->assertDontSee('samples/delivery-labels/sample-zh-TW.csv', false);
    }
}
