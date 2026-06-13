<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PrintJobStatus;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Jobs\Merchant\ProcessUploadJob;
use App\Models\Merchant;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Models\User;
use Database\Seeders\DomainSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Tests\TestCase;

class OrderPdfProcessingTest extends TestCase
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

    protected function tearDown(): void
    {
        gc_collect_cycles();

        parent::tearDown();
    }

    public function test_processes_sample_a_spreadsheet_into_order_pdf(): void
    {
        [, , $uploadJob] = $this->createOrderUploadJob('sample-a.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(
            UploadStatus::Completed,
            $uploadJob->status,
            'Upload failed: '.($uploadJob->error_message ?? 'no message'),
        );
        $this->assertCount(1, $uploadJob->printJobs);

        $printJob = $uploadJob->printJobs->first();
        $this->assertInstanceOf(PrintJob::class, $printJob);
        $this->assertSame(PrintJobStatus::Ready, $printJob->status);
        $this->assertSame('order_details', $printJob->module);
        $this->assertSame('generated', $printJob->metadata['layout_mode'] ?? null);
        $this->assertSame(3, $printJob->metadata['order_count'] ?? null);
        $this->assertSame(2, $printJob->metadata['page_count'] ?? null);
        Storage::disk('temp')->assertExists($printJob->output_path);

        $absolutePath = Storage::disk('temp')->path((string) $printJob->output_path);
        $this->assertSame(2, $this->countPdfPages($absolutePath));
    }

    public function test_processes_sample_b_spreadsheet_into_order_pdf_with_quantity(): void
    {
        [, , $uploadJob] = $this->createOrderUploadJob('sample-b.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(1, $printJob->metadata['order_count'] ?? null);
        $this->assertSame(1, $printJob->metadata['page_count'] ?? null);

        $document = is_array($printJob->metadata['document'] ?? null) ? $printJob->metadata['document'] : [];
        $orders = is_array($document['orders'] ?? null) ? $document['orders'] : [];
        $this->assertCount(1, $orders);
        $this->assertSame(2, $orders[0]['line_items'][0]['quantity'] ?? null);
        $this->assertSame(724, $orders[0]['line_items'][0]['line_total'] ?? null);
    }

    public function test_processes_multiple_spreadsheets_into_combined_order_pdf(): void
    {
        [, , $uploadJob] = $this->createOrderUploadJob(
            ['sample-a.xlsx', 'sample-b.xlsx'],
            orderOutputMode: 'combined',
        );

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);

        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(4, $printJob->metadata['order_count'] ?? null);
        $this->assertSame(2, $printJob->metadata['page_count'] ?? null);

        $processing = is_array($uploadJob->metadata['spreadsheet_processing'] ?? null)
            ? $uploadJob->metadata['spreadsheet_processing']
            : [];
        $this->assertCount(2, $processing);
        $this->assertSame('completed', $processing[0]['status'] ?? null);
        $this->assertSame('completed', $processing[1]['status'] ?? null);

        $absolutePath = Storage::disk('temp')->path((string) $printJob->output_path);
        $this->assertSame(2, $this->countPdfPages($absolutePath));
    }

    public function test_combined_mode_preserves_orders_from_duplicate_source_files(): void
    {
        [, , $uploadJob] = $this->createOrderUploadJob(
            ['sample-a.xlsx', 'sample-a.xlsx'],
            orderOutputMode: 'combined',
            originalNames: ['sample-a.xlsx', 'sample-a-copy.xlsx'],
        );

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $printJob = $uploadJob->printJobs->first();
        $this->assertSame(6, $printJob->metadata['order_count'] ?? null);
        $this->assertSame(3, $printJob->metadata['page_count'] ?? null);
    }

    public function test_combined_order_pdf_ignores_invalid_file_and_still_outputs_pdf(): void
    {
        $user = User::factory()->asMerchant()->create();
        $merchant = Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $invalidPath = base_path('public/samples/delivery-labels/sample-address.xlsx');
        $sampleFiles = ['sample-a.xlsx', 'invalid-address.xlsx'];
        $metadata = [
            'original_names' => $sampleFiles,
            'order_output_mode' => 'combined',
        ];

        $uploadJob = UploadJob::factory()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::Pending,
            'file_count' => 2,
            'metadata' => $metadata,
        ]);

        $spreadsheetFiles = [];
        foreach ($sampleFiles as $index => $sampleFile) {
            $sourcePath = $index === 1
                ? $invalidPath
                : base_path('public/samples/order-pdf/'.$sampleFile);
            $storedName = Str::uuid()->toString().'.'.pathinfo($sampleFile, PATHINFO_EXTENSION);
            $relativePath = "merchants/{$merchant->id}/jobs/{$uploadJob->id}/{$storedName}";
            Storage::disk('temp')->put($relativePath, file_get_contents($sourcePath) ?: '');
            $spreadsheetFiles[] = [
                'original_name' => $sampleFile,
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

        ProcessUploadJob::dispatchSync($uploadJob->fresh());
        $uploadJob->refresh();

        $this->assertSame(UploadStatus::CompletedWithErrors, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);

        $processing = collect(is_array($uploadJob->metadata['spreadsheet_processing'] ?? null)
            ? $uploadJob->metadata['spreadsheet_processing']
            : [])->keyBy('status');
        $this->assertTrue($processing->has('completed'));
        $this->assertTrue($processing->has('failed'));
    }

    public function test_processes_multiple_spreadsheets_into_separate_order_pdfs(): void
    {
        [, , $uploadJob] = $this->createOrderUploadJob(
            ['sample-a.xlsx', 'sample-b.xlsx'],
            orderOutputMode: 'separate',
        );

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertSame('separate', $uploadJob->metadata['order_output_mode'] ?? null);
        $this->assertCount(2, $uploadJob->printJobs);
    }

    public function test_merchant_can_download_generated_order_pdf(): void
    {
        [$user, , $uploadJob] = $this->createOrderUploadJob('sample-a.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $printJob = $uploadJob->fresh()->printJobs->first();

        $response = $this->actingAs($user)->get(route('printing.order_details.download', $printJob));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_merchant_can_preview_generated_order_pdf_inline(): void
    {
        [$user, , $uploadJob] = $this->createOrderUploadJob('sample-a.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $printJob = $uploadJob->fresh()->printJobs->first();

        $response = $this->actingAs($user)->get(route('printing.order_details.preview', $printJob));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_merchant_can_regenerate_order_pdf_print_job(): void
    {
        [$user, , $uploadJob] = $this->createOrderUploadJob('sample-a.xlsx');

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
        $this->assertNotNull($printJob->output_path);
        $this->assertNotNull($printJob->metadata['regenerated_at'] ?? null);
        Storage::disk('temp')->assertExists($printJob->output_path);
    }

    public function test_merchant_can_regenerate_completed_order_pdf_upload(): void
    {
        [$user, , $uploadJob] = $this->createOrderUploadJob('sample-a.xlsx');

        ProcessUploadJob::dispatchSync($uploadJob->fresh());

        $uploadJob->refresh();
        $firstPrintJobIds = $uploadJob->printJobs->pluck('id')->all();

        $response = $this->actingAs($user)->post(route('uploads.regenerate', $uploadJob));

        $response->assertRedirect(route('uploads.show', $uploadJob));

        $uploadJob->refresh();

        $this->assertSame(UploadStatus::Completed, $uploadJob->status);
        $this->assertCount(1, $uploadJob->printJobs);
        $this->assertNotSame($firstPrintJobIds, $uploadJob->printJobs->pluck('id')->all());
    }

    /**
     * @param  string|list<string>  $sampleFiles
     * @return array{0: User, 1: Merchant, 2: UploadJob}
     */
    private function createOrderUploadJob(
        string|array $sampleFiles = 'sample-a.xlsx',
        string $orderOutputMode = 'combined',
        ?array $originalNames = null,
    ): array {
        $sampleFiles = is_array($sampleFiles) ? $sampleFiles : [$sampleFiles];
        $displayNames = $originalNames ?? $sampleFiles;

        $user = User::factory()->asMerchant()->create();
        $merchant = Merchant::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'country_code' => 'TW',
        ]);

        $metadata = [
            'original_names' => $displayNames,
        ];

        if (count($sampleFiles) > 1) {
            $metadata['order_output_mode'] = $orderOutputMode;
        }

        $uploadJob = UploadJob::factory()->create([
            'merchant_id' => $merchant->id,
            'country_code' => 'TW',
            'user_id' => $user->id,
            'uploaded_by' => $user->id,
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::Pending,
            'file_count' => count($sampleFiles),
            'metadata' => $metadata,
        ]);

        $spreadsheetFiles = [];

        foreach ($sampleFiles as $index => $sampleFile) {
            $sourcePath = base_path('public/samples/order-pdf/'.$sampleFile);
            $storedName = Str::uuid()->toString().'.'.pathinfo($sampleFile, PATHINFO_EXTENSION);
            $relativePath = "merchants/{$merchant->id}/jobs/{$uploadJob->id}/{$storedName}";

            Storage::disk('temp')->put($relativePath, file_get_contents($sourcePath) ?: '');

            $spreadsheetFiles[] = [
                'original_name' => $displayNames[$index] ?? $sampleFile,
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

    private function countPdfPages(string $absolutePath): int
    {
        $pdf = new Fpdi('P', 'mm');

        return $pdf->setSourceFile($absolutePath);
    }
}
