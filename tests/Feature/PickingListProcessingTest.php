<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PrintJobStatus;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Jobs\Merchant\ProcessUploadJob;
use App\Models\Merchant;
use App\Models\PickingList;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PickingListProcessingTest extends TestCase
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

    public function test_processes_format_a_spreadsheet_into_picking_pdf(): void
    {
        [$user, , $uploadJob] = $this->createPickingUploadJob('sample-a.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(
            UploadStatus::Completed,
            $uploadJob->status,
            'Upload failed: '.($uploadJob->error_message ?? 'no message'),
        );
        $this->assertCount(1, $uploadJob->printJobs);
        $this->assertDatabaseCount('picking_lists', 1);

        $printJob = $uploadJob->printJobs->first();
        $this->assertInstanceOf(PrintJob::class, $printJob);
        $this->assertSame(PrintJobStatus::Ready, $printJob->status);
        $this->assertSame('picking_list', $printJob->module);
        $this->assertSame(3, $printJob->metadata['row_count'] ?? null);
        Storage::disk('temp')->assertExists($printJob->output_path);
    }

    public function test_processes_format_b_spreadsheet_into_picking_pdf(): void
    {
        [, , $uploadJob] = $this->createPickingUploadJob('sample-b.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(
            UploadStatus::Completed,
            $uploadJob->status,
            'Upload failed: '.($uploadJob->error_message ?? 'no message'),
        );
        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(1, $printJob->metadata['row_count'] ?? null);
        $this->assertSame(2, $printJob->metadata['total_units'] ?? null);
    }

    public function test_merchant_can_download_picking_list_print_job(): void
    {
        [$user, , $uploadJob] = $this->createPickingUploadJob('sample-a.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $printJob = $uploadJob->fresh()->printJobs->first();

        $response = $this->actingAs($user)->get(route('printing.picking_list.download', $printJob));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_processes_multiple_spreadsheets_into_separate_picking_pdfs(): void
    {
        [, , $uploadJob] = $this->createPickingUploadJob(
            ['sample-a.xlsx', 'sample-b.xlsx'],
            pickingOutputMode: 'separate',
        );

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(
            UploadStatus::Completed,
            $uploadJob->status,
            'Upload failed: '.($uploadJob->error_message ?? 'no message'),
        );
        $this->assertSame('separate', $uploadJob->metadata['picking_output_mode'] ?? null);
        $this->assertCount(2, $uploadJob->printJobs);
        $this->assertDatabaseCount('picking_lists', 2);

        $formatAJob = $uploadJob->printJobs->first(
            static fn (PrintJob $printJob): bool => ($printJob->metadata['row_count'] ?? 0) === 3,
        );
        $formatBJob = $uploadJob->printJobs->first(
            static fn (PrintJob $printJob): bool => ($printJob->metadata['row_count'] ?? 0) === 1,
        );

        $this->assertNotNull($formatAJob);
        $this->assertNotNull($formatBJob);
        $this->assertSame('separate', $formatAJob->metadata['picking_output_mode'] ?? null);
        $this->assertSame(2, $formatBJob->metadata['total_units'] ?? null);
        Storage::disk('temp')->assertExists($formatAJob->output_path);
        Storage::disk('temp')->assertExists($formatBJob->output_path);
    }

    public function test_processes_multiple_spreadsheets_into_combined_picking_pdf(): void
    {
        [, , $uploadJob] = $this->createPickingUploadJob(
            ['sample-a.xlsx', 'sample-b.xlsx'],
            pickingOutputMode: 'combined',
        );

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertSame('combined', $uploadJob->metadata['picking_output_mode'] ?? null);
        $this->assertCount(1, $uploadJob->printJobs);
        $this->assertDatabaseCount('picking_lists', 2);

        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(4, $printJob->metadata['row_count'] ?? null);
        $this->assertSame(5, $printJob->metadata['total_units'] ?? null);

        $processing = is_array($uploadJob->metadata['spreadsheet_processing'] ?? null)
            ? $uploadJob->metadata['spreadsheet_processing']
            : [];
        $this->assertCount(2, $processing);
        $this->assertSame('completed', $processing[0]['status'] ?? null);
        $this->assertSame('completed', $processing[1]['status'] ?? null);
    }

    public function test_combined_mode_processes_valid_files_and_reports_invalid_file(): void
    {
        [, , $uploadJob] = $this->createPickingUploadJob(
            ['sample-a.xlsx', 'invalid-address.xlsx'],
            pickingOutputMode: 'combined',
            invalidFilePath: 'public/samples/delivery-labels/sample-address.xlsx',
        );

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::CompletedWithErrors, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);
        $this->assertDatabaseCount('picking_lists', 2);

        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(3, $printJob->metadata['row_count'] ?? null);

        $processing = collect(is_array($uploadJob->metadata['spreadsheet_processing'] ?? null)
            ? $uploadJob->metadata['spreadsheet_processing']
            : [])->keyBy('status');

        $this->assertTrue($processing->has('completed'));
        $this->assertTrue($processing->has('failed'));
    }

    public function test_separate_mode_processes_valid_file_and_reports_invalid_file(): void
    {
        [, , $uploadJob] = $this->createPickingUploadJob(
            ['sample-a.xlsx', 'invalid-address.xlsx'],
            pickingOutputMode: 'separate',
            invalidFilePath: 'public/samples/delivery-labels/sample-address.xlsx',
        );

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::CompletedWithErrors, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);
        $this->assertDatabaseCount('picking_lists', 2);

        $failedList = \App\Models\PickingList::query()
            ->where('upload_job_id', $uploadJob->id)
            ->where('status', UploadStatus::Failed)
            ->first();

        $this->assertNotNull($failedList);
        $this->assertStringContainsString('tracking_number', (string) ($failedList->metadata['error_message'] ?? ''));

        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(3, $printJob->metadata['row_count'] ?? null);
    }

    public function test_sample_spreadsheet_preview_endpoint_returns_rows(): void
    {
        $user = User::factory()->asMerchant()->create();

        $response = $this->actingAs($user)->getJson(route('uploads.samples.preview', [
            'path' => 'samples/picking-list/sample-a.xlsx',
        ]));

        $response->assertOk();
        $response->assertJsonPath('headers.0', 'tracking_number');
        $response->assertJsonCount(3, 'rows');
    }

    public function test_merchant_can_regenerate_picking_list_print_job(): void
    {
        [$user, , $uploadJob] = $this->createPickingUploadJob('sample-a.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $printJob = $uploadJob->fresh()->printJobs->first();

        $response = $this->actingAs($user)->postJson(
            route('uploads.print_jobs.regenerate', [$uploadJob, $printJob]),
        );

        $response->assertOk();
        $response->assertJsonPath('output.id', $printJob->id);
        $response->assertJsonPath('output.can_regenerate', true);

        $printJob->refresh();
        $this->assertSame(PrintJobStatus::Ready, $printJob->status);
        $this->assertNotNull($printJob->metadata['regenerated_at'] ?? null);
        Storage::disk('temp')->assertExists($printJob->output_path);
    }

    /**
     * @param  string|list<string>  $sampleFileNames
     * @return array{0: User, 1: Merchant, 2: UploadJob}
     */
    private function createPickingUploadJob(
        string|array $sampleFileNames,
        ?string $pickingOutputMode = null,
        ?string $invalidFilePath = null,
    ): array {
        $sampleFileNames = is_array($sampleFileNames) ? $sampleFileNames : [$sampleFileNames];
        $user = User::factory()->asMerchant()->create();
        $merchant = Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $metadata = [
            'original_names' => $sampleFileNames,
        ];

        if (count($sampleFileNames) > 1 && $pickingOutputMode !== null) {
            $metadata['picking_output_mode'] = $pickingOutputMode;
        }

        $uploadJob = UploadJob::factory()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::PickingList,
            'status' => UploadStatus::Pending,
            'file_count' => count($sampleFileNames),
            'metadata' => $metadata,
        ]);

        $spreadsheetFiles = [];

        foreach ($sampleFileNames as $sampleFileName) {
            $relativePath = "merchants/{$merchant->id}/jobs/{$uploadJob->id}/".Str::uuid()->toString().'.xlsx';
            $source = $invalidFilePath !== null && str_contains($sampleFileName, 'invalid')
                ? base_path($invalidFilePath)
                : base_path('public/samples/picking-list/'.$sampleFileName);
            Storage::disk('temp')->put($relativePath, file_get_contents($source) ?: '');

            $spreadsheetFiles[] = [
                'original_name' => $sampleFileName,
                'disk' => 'temp',
                'path' => $relativePath,
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'size_bytes' => Storage::disk('temp')->size($relativePath),
            ];
        }

        $uploadJob->update([
            'metadata' => [
                ...$metadata,
                'spreadsheet_files' => $spreadsheetFiles,
            ],
        ]);

        return [$user, $merchant, $uploadJob];
    }
}
