<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role as RoleEnum;
use App\Enums\UploadJobType;
use App\Jobs\Merchant\ProcessUploadJob;
use App\Models\Merchant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        Storage::fake('temp');
    }

    public function test_merchant_can_upload_order_pdf_spreadsheet(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignPrimaryRole(RoleEnum::Merchant);
        Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $response = $this->actingAs($user)->post(route('uploads.store'), [
            'type' => UploadJobType::OrderPdf->value,
            'files' => [
                UploadedFile::fake()->create('order.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('upload_jobs', [
            'uploaded_by' => $user->id,
            'type' => UploadJobType::OrderPdf->value,
        ]);
    }

    public function test_guest_cannot_access_uploads(): void
    {
        $this->get(route('uploads.index'))->assertRedirect(route('login'));
    }

    public function test_order_pdf_upload_dispatches_processing_job(): void
    {
        Queue::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignPrimaryRole(RoleEnum::Merchant);
        Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $this->actingAs($user)->post(route('uploads.store'), [
            'type' => UploadJobType::OrderPdf->value,
            'files' => [
                UploadedFile::fake()->create('order.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ],
        ])->assertRedirect();

        Queue::assertPushed(ProcessUploadJob::class);
    }
}
