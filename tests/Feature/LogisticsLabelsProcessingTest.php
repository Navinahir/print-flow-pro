<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PrintJobStatus;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Jobs\Merchant\ProcessUploadJob;
use App\Models\Merchant;
use App\Models\PdfUpload;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Tests\Support\PdfFixtureFactory;
use Tests\TestCase;

class LogisticsLabelsProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DomainSettingSeeder::class);

        $testingRoot = storage_path('framework/testing/disks/temp');

        if (is_dir($testingRoot)) {
            File::deleteDirectory($testingRoot);
        }

        Storage::fake('temp');
    }

    public function test_processes_thermal_upload_into_print_jobs(): void
    {
        [$user, $merchant, $uploadJob, $relativePath] = $this->createThermalUploadJob();

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);

        $printJob = $uploadJob->printJobs->first();
        $this->assertInstanceOf(PrintJob::class, $printJob);
        $this->assertSame(PrintJobStatus::Ready, $printJob->status);
        $this->assertNotNull($printJob->output_path);
        $this->assertSame(210.0, $printJob->output_width_mm);
        $this->assertSame(297.0, $printJob->output_height_mm);
        $this->assertSame('a4_single', $printJob->metadata['layout_mode'] ?? null);
        Storage::disk('temp')->assertExists($printJob->output_path);
    }

    public function test_processes_multi_page_thermal_upload_into_single_a4_sheet(): void
    {
        [$user, $merchant, $uploadJob] = $this->createThermalUploadJob(pages: 3);

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);

        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(PrintJobStatus::Ready, $printJob->status);
        $this->assertSame(210.0, $printJob->output_width_mm);
        $this->assertSame(297.0, $printJob->output_height_mm);
        $this->assertSame('a4_multi', $printJob->metadata['layout_mode'] ?? null);
        $this->assertSame(3, $printJob->metadata['label_count'] ?? null);
        $this->assertSame(1, $printJob->metadata['page_count'] ?? null);
        Storage::disk('temp')->assertExists($printJob->output_path);
    }

    public function test_processes_more_than_four_labels_into_single_multi_page_output(): void
    {
        [, , $uploadJob] = $this->createThermalUploadJob(pages: 5);

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);

        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(5, $printJob->metadata['label_count'] ?? null);
        $this->assertSame(2, $printJob->metadata['page_count'] ?? null);

        $absolutePath = Storage::disk('temp')->path((string) $printJob->output_path);
        $pdf = new Fpdi('P', 'mm');
        $this->assertSame(2, $pdf->setSourceFile($absolutePath));
    }

    public function test_rejects_a4_pdf_during_processing(): void
    {
        [, , $uploadJob] = $this->createThermalUploadJob(useA4: true);

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Failed, $uploadJob->status);
        $this->assertSame(0, $uploadJob->printJobs()->count());
        $this->assertNotNull($uploadJob->error_message);
    }

    public function test_merchant_can_download_normalized_print_job(): void
    {
        [$user, , $uploadJob] = $this->createThermalUploadJob();

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $printJob = $uploadJob->fresh()->printJobs->first();

        $response = $this->actingAs($user)->get(route('printing.logistics_labels.download', $printJob));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_merchant_can_preview_normalized_print_job_inline(): void
    {
        [$user, , $uploadJob] = $this->createThermalUploadJob();

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $printJob = $uploadJob->fresh()->printJobs->first();

        $response = $this->actingAs($user)->get(route('printing.logistics_labels.preview', $printJob));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_logistics_workspace_lists_print_jobs(): void
    {
        [$user] = $this->createThermalUploadJob();

        ProcessUploadJob::dispatchSync(UploadJob::query()->latest()->first());

        $response = $this->actingAs($user)->get(route('printing.logistics_labels.index'));

        $response->assertOk();
        $response->assertSee('print-job-', false);
    }

    public function test_merchant_can_regenerate_completed_thermal_upload(): void
    {
        [$user, , $uploadJob] = $this->createThermalUploadJob();

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();
        $firstPrintJobIds = $uploadJob->printJobs->pluck('id')->all();
        $this->assertNotEmpty($firstPrintJobIds);

        $response = $this->actingAs($user)->post(route('uploads.regenerate', $uploadJob));

        $response->assertRedirect(route('uploads.show', $uploadJob));

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);
        $this->assertNotSame($firstPrintJobIds, $uploadJob->printJobs->pluck('id')->all());
    }

    public function test_processes_multiple_thermal_uploads_into_separate_print_jobs(): void
    {
        [$user, , $uploadJob] = $this->createThermalUploadJobWithMultipleFiles(
            pagesPerFile: [1, 3],
            thermalOutputMode: 'separate',
        );

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertSame('separate', $uploadJob->metadata['thermal_output_mode'] ?? null);
        $this->assertCount(2, $uploadJob->printJobs);

        $singleLabelJob = $uploadJob->printJobs->first(
            static fn (PrintJob $printJob): bool => ($printJob->metadata['label_count'] ?? 0) === 1,
        );
        $multiLabelJob = $uploadJob->printJobs->first(
            static fn (PrintJob $printJob): bool => ($printJob->metadata['label_count'] ?? 0) === 3,
        );

        $this->assertNotNull($singleLabelJob);
        $this->assertNotNull($multiLabelJob);
        $this->assertSame('a4_single', $singleLabelJob->metadata['layout_mode'] ?? null);
        $this->assertSame('a4_multi', $multiLabelJob->metadata['layout_mode'] ?? null);
        $this->assertSame('separate', $singleLabelJob->metadata['thermal_output_mode'] ?? null);
        Storage::disk('temp')->assertExists($singleLabelJob->output_path);
        Storage::disk('temp')->assertExists($multiLabelJob->output_path);
    }

    public function test_merchant_can_regenerate_single_print_job(): void
    {
        [$user, , $uploadJob] = $this->createThermalUploadJob();

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $printJob = $uploadJob->fresh()->printJobs->first();

        $response = $this->actingAs($user)->postJson(
            route('uploads.print_jobs.regenerate', [$uploadJob, $printJob]),
        );

        $response->assertOk();
        $response->assertJsonPath('output.id', $printJob->id);

        $printJob->refresh();
        $this->assertSame(PrintJobStatus::Ready, $printJob->status);
        $this->assertNotNull($printJob->output_path);
        Storage::disk('temp')->assertExists($printJob->output_path);
    }

    public function test_merchant_can_delete_upload_and_related_files(): void
    {
        [$user, , $uploadJob] = $this->createThermalUploadJob();

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();
        $printJobPath = $uploadJob->printJobs->first()->output_path;
        $sourcePath = $uploadJob->pdfUploads->first()->path;
        $uploadJobId = $uploadJob->id;

        Storage::disk('temp')->assertExists($printJobPath);
        Storage::disk('temp')->assertExists($sourcePath);

        $response = $this->actingAs($user)->deleteJson(route('uploads.destroy', $uploadJob));

        $response->assertOk();
        $response->assertJsonPath('message', __('merchant.uploads.delete.success'));

        $this->assertDatabaseMissing('upload_jobs', ['id' => $uploadJobId]);
        $this->assertDatabaseMissing('pdf_uploads', ['upload_job_id' => $uploadJobId]);
        $this->assertDatabaseMissing('print_jobs', ['upload_job_id' => $uploadJobId]);
        Storage::disk('temp')->assertMissing($printJobPath);
        Storage::disk('temp')->assertMissing($sourcePath);
    }

    /**
     * @return array{0: User, 1: Merchant, 2: UploadJob, 3: string}
     */
    private function createThermalUploadJob(bool $useA4 = false, int $pages = 1): array
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

        $relativePath = "merchants/{$merchant->id}/jobs/{$uploadJob->id}/".Str::uuid()->toString().'.pdf';

        if ($useA4) {
            PdfFixtureFactory::putA4Label($relativePath);
        } elseif ($pages > 1) {
            PdfFixtureFactory::putMultiPageThermalLabel($relativePath, $pages);
        } else {
            PdfFixtureFactory::putThermalLabel($relativePath);
        }

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

        return [$user, $merchant, $uploadJob, $relativePath];
    }

    /**
     * @param  list<int>  $pagesPerFile
     * @return array{0: User, 1: Merchant, 2: UploadJob}
     */
    private function createThermalUploadJobWithMultipleFiles(
        array $pagesPerFile = [1, 1],
        string $thermalOutputMode = 'combined',
    ): array {
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
            'file_count' => count($pagesPerFile),
            'metadata' => [
                'thermal_output_mode' => $thermalOutputMode,
                'original_names' => array_map(
                    static fn (int $index): string => 'thermal-'.($index + 1).'.pdf',
                    array_keys($pagesPerFile),
                ),
            ],
        ]);

        foreach ($pagesPerFile as $index => $pages) {
            $relativePath = "merchants/{$merchant->id}/jobs/{$uploadJob->id}/".Str::uuid()->toString().'.pdf';

            if ($pages > 1) {
                PdfFixtureFactory::putMultiPageThermalLabel($relativePath, $pages);
            } else {
                PdfFixtureFactory::putThermalLabel($relativePath);
            }

            PdfUpload::query()->create([
                'merchant_id' => $merchant->id,
                'country_code' => 'TW',
                'upload_job_id' => $uploadJob->id,
                'original_name' => 'thermal-'.($index + 1).'.pdf',
                'disk' => 'temp',
                'path' => $relativePath,
                'mime_type' => 'application/pdf',
                'size_bytes' => Storage::disk('temp')->size($relativePath),
                'status' => UploadStatus::Pending,
            ]);
        }

        return [$user, $merchant, $uploadJob];
    }
}
