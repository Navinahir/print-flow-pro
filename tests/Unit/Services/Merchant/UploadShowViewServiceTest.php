<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Merchant;

use App\DTOs\Merchant\Upload\UploadPreviewResult;
use App\Enums\PrintJobStatus;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Services\Merchant\UploadShowViewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UploadShowViewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepares_a4_preview_config_for_completed_thermal_job(): void
    {
        $user = \App\Models\User::factory()->asMerchant()->create();
        $merchant = \App\Models\Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $job = UploadJob::factory()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::ThermalLabel,
            'status' => UploadStatus::Completed,
        ]);

        PrintJob::query()->create([
            'upload_job_id' => $job->id,
            'merchant_id' => $job->merchant_id,
            'country_code' => 'TW',
            'module' => 'logistics_labels',
            'status' => PrintJobStatus::Ready,
            'source_page_number' => 1,
            'output_disk' => 'temp',
            'output_path' => 'test/output.pdf',
            'output_width_mm' => 210,
            'output_height_mm' => 297,
            'metadata' => [
                'layout_mode' => 'a4_single',
                'sheet_number' => 1,
                'label_count' => 1,
                'source_pages' => [],
            ],
        ]);

        $job->load('printJobs');
        $service = app(UploadShowViewService::class);
        $view = $service->prepare($job, new UploadPreviewResult(
            available: true,
            preview: null,
            previewType: null,
        ));

        $this->assertTrue($view['use_pdf_preview']);
        $this->assertSame(210.0, $view['preview_config']->widthMm);
        $this->assertSame(297.0, $view['preview_config']->heightMm);
        $this->assertSame('A4 sheet 1', $view['print_outputs'][0]['title']);
    }
}
