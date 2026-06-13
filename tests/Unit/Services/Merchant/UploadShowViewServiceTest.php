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
        $this->assertSame(__('merchant.uploads.detail.thermal_combined_title'), $view['print_outputs'][0]['title']);
    }

    public function test_shows_completed_status_on_all_spreadsheet_source_files(): void
    {
        $user = \App\Models\User::factory()->asMerchant()->create();
        $merchant = \App\Models\Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $spreadsheetFiles = [
            [
                'original_name' => 'file-a.xlsx',
                'disk' => 'temp',
                'path' => 'merchants/1/jobs/1/a.xlsx',
                'size_bytes' => 7000,
            ],
            [
                'original_name' => 'file-b.xlsx',
                'disk' => 'temp',
                'path' => 'merchants/1/jobs/1/b.xlsx',
                'size_bytes' => 7100,
            ],
        ];

        $job = UploadJob::factory()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::PickingList,
            'status' => UploadStatus::Completed,
            'metadata' => [
                'spreadsheet_files' => $spreadsheetFiles,
                'spreadsheet_processing' => [
                    ['source_path' => 'merchants/1/jobs/1/a.xlsx', 'status' => 'completed', 'error_message' => null],
                    ['source_path' => 'merchants/1/jobs/1/b.xlsx', 'status' => 'completed', 'error_message' => null],
                ],
            ],
        ]);

        $service = app(UploadShowViewService::class);
        $view = $service->prepare($job, new UploadPreviewResult(
            available: false,
            preview: null,
            previewType: null,
        ));

        $this->assertCount(2, $view['source_files']);
        $this->assertSame('completed', $view['source_files'][0]['processing_status']);
        $this->assertSame('completed', $view['source_files'][1]['processing_status']);
        $this->assertSame(
            __('merchant.uploads.detail.source_file_status.completed'),
            $view['source_files'][0]['processing_status_label'],
        );
        $this->assertSame(
            __('merchant.uploads.detail.source_file_status.completed'),
            $view['source_files'][1]['processing_status_label'],
        );
    }

    public function test_shows_failed_status_only_on_invalid_spreadsheet_file(): void
    {
        $user = \App\Models\User::factory()->asMerchant()->create();
        $merchant = \App\Models\Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $spreadsheetFiles = [
            [
                'original_name' => 'valid.xlsx',
                'disk' => 'temp',
                'path' => 'merchants/1/jobs/2/valid.xlsx',
                'size_bytes' => 7000,
            ],
            [
                'original_name' => 'invalid.xlsx',
                'disk' => 'temp',
                'path' => 'merchants/1/jobs/2/invalid.xlsx',
                'size_bytes' => 7100,
            ],
        ];

        $job = UploadJob::factory()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::CompletedWithErrors,
            'metadata' => [
                'spreadsheet_files' => $spreadsheetFiles,
                'spreadsheet_processing' => [
                    ['source_path' => 'merchants/1/jobs/2/valid.xlsx', 'status' => 'completed', 'error_message' => null],
                    [
                        'source_path' => 'merchants/1/jobs/2/invalid.xlsx',
                        'status' => 'failed',
                        'error_message' => 'Invalid spreadsheet format.',
                    ],
                ],
                'file_errors' => [
                    [
                        'source_name' => 'invalid.xlsx',
                        'source_path' => 'merchants/1/jobs/2/invalid.xlsx',
                        'message' => 'Invalid spreadsheet format.',
                    ],
                ],
            ],
        ]);

        $service = app(UploadShowViewService::class);
        $view = $service->prepare($job, new UploadPreviewResult(
            available: false,
            preview: null,
            previewType: null,
        ));

        $this->assertSame('completed', $view['source_files'][0]['processing_status']);
        $this->assertSame('failed', $view['source_files'][1]['processing_status']);
        $this->assertNull($view['source_files'][0]['error_message']);
        $this->assertSame('Invalid spreadsheet format.', $view['source_files'][1]['error_message']);
    }
}
