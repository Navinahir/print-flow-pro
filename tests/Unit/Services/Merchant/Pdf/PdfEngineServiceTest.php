<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant\Pdf;

use App\Actions\Merchant\Pdf\PreparePdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\Enums\PdfProcessingMode;
use App\Enums\PdfProcessingStatus;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\Merchant;
use App\Models\PdfUpload;
use App\Models\UploadJob;
use App\Models\User;
use App\Services\Merchant\Pdf\PdfEngineService;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\PdfFixtureFactory;
use Tests\TestCase;

class PdfEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);
        Storage::fake('temp');
    }

    public function test_runs_logistics_normalization_for_valid_thermal_pdf(): void
    {
        $user = User::factory()->asMerchant()->create();
        $merchant = Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $uploadJob = UploadJob::factory()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::ThermalLabel,
            'status' => UploadStatus::Pending,
        ]);

        $relativePath = "merchants/{$merchant->id}/jobs/{$uploadJob->id}/thermal.pdf";
        PdfFixtureFactory::putThermalLabel($relativePath);

        PdfUpload::query()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'upload_job_id' => $uploadJob->id,
            'original_name' => 'thermal.pdf',
            'disk' => 'temp',
            'path' => $relativePath,
            'mime_type' => 'application/pdf',
            'size_bytes' => Storage::disk('temp')->size($relativePath),
            'status' => UploadStatus::Pending,
        ]);

        $context = (new PreparePdfProcessingContext)->execute($uploadJob);

        $result = app(PdfEngineService::class)->process($context);

        $this->assertTrue($result->success);
        $this->assertSame(PdfProcessingStatus::Completed, $result->context->status);
        $this->assertNotEmpty($result->context->detectedBoundaries);
        $this->assertNotNull($result->context->canvas);
        $this->assertTrue($result->normalization?->implemented);
        $this->assertTrue($result->normalization?->success);
        $this->assertSame(1, $uploadJob->fresh()->printJobs()->count());
    }

    public function test_fails_when_no_sources_provided(): void
    {
        $context = new PdfProcessingContext(
            uploadJobId: 1,
            merchantId: 1,
            countryCode: 'TW',
            mode: PdfProcessingMode::ThermalLabel,
        );

        $result = app(PdfEngineService::class)->process($context);

        $this->assertFalse($result->success);
        $this->assertSame(PdfProcessingStatus::Failed, $result->status);
    }
}
