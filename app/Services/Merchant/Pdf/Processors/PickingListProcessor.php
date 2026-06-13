<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Processors;

use App\Contracts\Merchant\Pdf\PdfProcessorInterface;
use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfTempPath;
use App\DTOs\Merchant\PickingList\PickingListDocument;
use App\DTOs\Merchant\PickingList\PickingListRow;
use App\Enums\PdfProcessingMode;
use App\Enums\PickingOutputMode;
use App\Enums\PrintJobStatus;
use App\Enums\UploadStatus;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use App\Models\PickingList;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Models\User;
use App\Services\Merchant\PickingList\PickingListPdfRenderer;
use App\Services\Merchant\PickingList\PickingListSpreadsheetReader;
use App\Services\Merchant\Pdf\PdfTempStorageService;
use App\Services\Merchant\Spreadsheet\SpreadsheetBatchRowParser;
use App\Services\Merchant\Spreadsheet\SpreadsheetProcessingMetadataWriter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PickingListProcessor implements PdfProcessorInterface
{
    public function __construct(
        private readonly PickingListSpreadsheetReader $spreadsheetReader,
        private readonly PickingListPdfRenderer $pdfRenderer,
        private readonly PdfTempStorageService $tempStorage,
        private readonly SpreadsheetBatchRowParser $spreadsheetBatchRowParser,
        private readonly SpreadsheetProcessingMetadataWriter $spreadsheetProcessingWriter,
    ) {}

    public function supports(PdfProcessingMode $mode): bool
    {
        return $mode === PdfProcessingMode::PickingListExport;
    }

    public function normalize(PdfProcessingContext $context): PdfNormalizationResult
    {
        $uploadJob = UploadJob::query()
            ->with(['uploadedBy', 'merchant.user'])
            ->findOrFail($context->uploadJobId);

        $sourceFiles = $this->collectSpreadsheetSources($uploadJob);
        $outputMode = PickingOutputMode::fromUploadMetadata($uploadJob->metadata, count($sourceFiles));

        if ($outputMode === PickingOutputMode::Separate && count($sourceFiles) > 1) {
            return $this->normalizeSeparate($context, $uploadJob, $sourceFiles, $outputMode);
        }

        return $this->normalizeCombined($context, $uploadJob, $sourceFiles, $outputMode);
    }

    public function regeneratePrintJob(PrintJob $printJob, PdfProcessingContext $context): PrintJob
    {
        $uploadJob = UploadJob::query()
            ->with(['uploadedBy', 'merchant.user'])
            ->findOrFail($context->uploadJobId);

        $sourceFiles = $this->resolveSourceFilesFromMetadata($printJob);

        if (is_string($printJob->output_path) && $printJob->output_path !== '') {
            Storage::disk($printJob->output_disk)->delete($printJob->output_path);
        }

        $rows = $this->parseRows($sourceFiles);
        $document = $this->buildDocument($uploadJob, $rows, $sourceFiles);
        $sheetNumber = (int) ($printJob->metadata['sheet_number'] ?? $printJob->source_page_number);
        $outputRelative = $this->buildOutputRelativePath($context, $sheetNumber);
        $outputAbsolute = $this->tempStorage->absolutePath(
            new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $outputRelative),
        );

        $this->pdfRenderer->renderToFile($document, $outputAbsolute);

        $outputModeValue = (string) ($printJob->metadata['picking_output_mode'] ?? PickingOutputMode::Combined->value);
        $outputMode = PickingOutputMode::tryFrom($outputModeValue) ?? PickingOutputMode::Combined;

        $printJob->update([
            'status' => PrintJobStatus::Ready,
            'output_path' => $outputRelative,
            'checksum' => hash_file('sha256', $outputAbsolute) ?: null,
            'expires_at' => now()->addMinutes((int) config('pdf.output_ttl_minutes', 10)),
            'error_message' => null,
            'metadata' => [
                ...($printJob->metadata ?? []),
                'layout_mode' => 'picking_sheet',
                'sheet_number' => $sheetNumber,
                'row_count' => count($document->rows),
                'total_units' => $document->totalUnits,
                'source_file_count' => count($sourceFiles),
                'source_file_name' => $sourceFiles[0]['original_name'],
                'original_name' => $this->buildOutputFileName($sourceFiles),
                'picking_output_mode' => $outputMode->value,
                'document' => $document->toArray(),
                'source_files' => $this->serializeSourceFiles($sourceFiles),
                'regenerated_at' => now()->toIso8601String(),
            ],
        ]);

        PickingList::query()
            ->where('upload_job_id', $context->uploadJobId)
            ->where('source_path', $sourceFiles[0]['path'])
            ->update([
                'status' => UploadStatus::Completed,
                'row_count' => count($document->rows),
                'output_disk' => (string) config('pdf.temp_disk', 'temp'),
                'output_path' => $outputRelative,
                'metadata' => [
                    'source_files' => $this->serializeSourceFiles($sourceFiles),
                    'document' => $document->toArray(),
                    'picking_output_mode' => $outputMode->value,
                    'regenerated_at' => now()->toIso8601String(),
                ],
            ]);

        return $printJob->fresh();
    }

    /**
     * @return list<array{original_name: string, disk: string, path: string, absolute_path: string}>
     */
    private function resolveSourceFilesFromMetadata(PrintJob $printJob): array
    {
        $serialized = is_array($printJob->metadata['source_files'] ?? null)
            ? $printJob->metadata['source_files']
            : [];

        if ($serialized === []) {
            throw PdfNormalizationException::failed('Print job is missing source file metadata.');
        }

        $sources = [];

        foreach ($serialized as $file) {
            if (! is_array($file)) {
                continue;
            }

            $path = (string) ($file['path'] ?? '');
            $disk = (string) ($file['disk'] ?? 'temp');
            $originalName = (string) ($file['original_name'] ?? basename($path));

            if ($path === '') {
                continue;
            }

            $absolutePath = $this->tempStorage->absolutePath(new PdfTempPath($disk, $path));

            if (! is_readable($absolutePath)) {
                throw PdfNormalizationException::failed("Spreadsheet source is not readable: {$originalName}");
            }

            $sources[] = [
                'original_name' => $originalName,
                'disk' => $disk,
                'path' => $path,
                'absolute_path' => $absolutePath,
            ];
        }

        if ($sources === []) {
            throw PdfNormalizationException::failed('No readable spreadsheet sources found for regeneration.');
        }

        return $sources;
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     */
    private function normalizeCombined(
        PdfProcessingContext $context,
        UploadJob $uploadJob,
        array $sourceFiles,
        PickingOutputMode $outputMode,
    ): PdfNormalizationResult {
        $batch = $this->spreadsheetBatchRowParser->parse($sourceFiles);

        if ($batch->successfulFiles === []) {
            $firstMessage = $batch->fileErrors[0]['message'] ?? __('merchant.picking_list.errors.empty_spreadsheet');

            $this->spreadsheetProcessingWriter->record(
                $context->uploadJobId,
                $sourceFiles,
                $batch->processingResults,
            );

            return new PdfNormalizationResult(
                implemented: true,
                success: false,
                metadata: [
                    'file_errors' => $batch->fileErrors,
                    'picking_output_mode' => $outputMode->value,
                ],
                message: $firstMessage,
            );
        }

        $document = $this->buildDocument($uploadJob, $batch->rows, $batch->successfulFiles);

        $outputRelative = $this->buildOutputRelativePath($context);
        $outputAbsolute = $this->tempStorage->absolutePath(
            new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $outputRelative),
        );

        $this->pdfRenderer->renderToFile($document, $outputAbsolute);

        $expiresAt = now()->addMinutes((int) config('pdf.output_ttl_minutes', 10));

        $printJob = DB::transaction(function () use (
            $context,
            $uploadJob,
            $sourceFiles,
            $batch,
            $document,
            $outputRelative,
            $outputAbsolute,
            $expiresAt,
            $outputMode,
        ): PrintJob {
            $failedPaths = array_column($batch->fileErrors, 'source_path');

            foreach ($sourceFiles as $sourceFile) {
                if (in_array($sourceFile['path'], $failedPaths, true)) {
                    $error = collect($batch->fileErrors)
                        ->first(static fn (array $entry): bool => $entry['source_path'] === $sourceFile['path']);

                    PickingList::query()->create([
                        'merchant_id' => $context->merchantId,
                        'country_code' => $context->countryCode,
                        'upload_job_id' => $context->uploadJobId,
                        'source_name' => $sourceFile['original_name'],
                        'source_disk' => $sourceFile['disk'],
                        'source_path' => $sourceFile['path'],
                        'status' => UploadStatus::Failed,
                        'row_count' => 0,
                        'output_disk' => null,
                        'output_path' => null,
                        'metadata' => [
                            'source_files' => $this->serializeSourceFiles([$sourceFile]),
                            'picking_output_mode' => $outputMode->value,
                            'error_message' => (string) ($error['message'] ?? ''),
                        ],
                    ]);

                    continue;
                }

                $rowCount = count(array_filter(
                    $batch->rows,
                    static fn (PickingListRow $row): bool => $row->sourceFileName === $sourceFile['original_name'],
                ));

                PickingList::query()->create([
                    'merchant_id' => $context->merchantId,
                    'country_code' => $context->countryCode,
                    'upload_job_id' => $context->uploadJobId,
                    'source_name' => $sourceFile['original_name'],
                    'source_disk' => $sourceFile['disk'],
                    'source_path' => $sourceFile['path'],
                    'status' => UploadStatus::Completed,
                    'row_count' => $rowCount,
                    'output_disk' => (string) config('pdf.temp_disk', 'temp'),
                    'output_path' => $outputRelative,
                    'metadata' => [
                        'source_files' => $this->serializeSourceFiles($batch->successfulFiles),
                        'document' => $document->toArray(),
                        'picking_output_mode' => $outputMode->value,
                    ],
                ]);
            }

            return $this->createPrintJob(
                context: $context,
                sourceFiles: $batch->successfulFiles,
                document: $document,
                outputRelative: $outputRelative,
                outputAbsolute: $outputAbsolute,
                expiresAt: $expiresAt,
                outputMode: $outputMode,
                sheetNumber: 1,
            );
        });

        $this->spreadsheetProcessingWriter->record(
            $context->uploadJobId,
            $sourceFiles,
            $batch->processingResults,
        );

        return new PdfNormalizationResult(
            implemented: true,
            success: true,
            outputRelativePaths: [$outputRelative],
            metadata: [
                'print_job_ids' => [$printJob->id],
                'processed_pages' => 1,
                'layout_mode' => 'picking_sheet',
                'row_count' => count($document->rows),
                'total_units' => $document->totalUnits,
                'picking_output_mode' => $outputMode->value,
                'file_errors' => $batch->fileErrors,
                'partial_success' => $batch->fileErrors !== [],
                'failed_file_count' => count($batch->fileErrors),
            ],
            message: $batch->fileErrors !== []
                ? __('merchant.pdf.normalization.picking_list_partial_complete', [
                    'rows' => count($document->rows),
                    'failed' => count($batch->fileErrors),
                ])
                : __('merchant.pdf.normalization.picking_list_complete', [
                    'rows' => count($document->rows),
                ]),
        );
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     */
    private function normalizeSeparate(
        PdfProcessingContext $context,
        UploadJob $uploadJob,
        array $sourceFiles,
        PickingOutputMode $outputMode,
    ): PdfNormalizationResult {
        $outputPaths = [];
        $printJobIds = [];
        $fileErrors = [];
        $totalRows = 0;
        $totalUnits = 0;
        $expiresAt = now()->addMinutes((int) config('pdf.output_ttl_minutes', 10));
        $processingResults = [];

        DB::transaction(function () use (
            $context,
            $uploadJob,
            $sourceFiles,
            $outputMode,
            $expiresAt,
            &$outputPaths,
            &$printJobIds,
            &$fileErrors,
            &$totalRows,
            &$totalUnits,
            &$processingResults,
        ): void {
            foreach ($sourceFiles as $index => $sourceFile) {
                $sheetNumber = $index + 1;

                try {
                    $result = $this->processSingleSourceFile(
                        context: $context,
                        uploadJob: $uploadJob,
                        sourceFile: $sourceFile,
                        outputMode: $outputMode,
                        sheetNumber: $sheetNumber,
                        expiresAt: $expiresAt,
                    );

                    $outputPaths[] = $result['output_relative'];
                    $printJobIds[] = $result['print_job_id'];
                    $totalRows += $result['row_count'];
                    $totalUnits += $result['total_units'];
                    $processingResults[] = [
                        'source_path' => $sourceFile['path'],
                        'status' => 'completed',
                        'error_message' => null,
                    ];
                } catch (Throwable $exception) {
                    $message = $exception instanceof PdfNormalizationException
                        ? $exception->getMessage()
                        : __('merchant.picking_list.errors.invalid_format', ['detail' => $exception->getMessage()]);

                    PickingList::query()->create([
                        'merchant_id' => $context->merchantId,
                        'country_code' => $context->countryCode,
                        'upload_job_id' => $context->uploadJobId,
                        'source_name' => $sourceFile['original_name'],
                        'source_disk' => $sourceFile['disk'],
                        'source_path' => $sourceFile['path'],
                        'status' => UploadStatus::Failed,
                        'row_count' => 0,
                        'output_disk' => null,
                        'output_path' => null,
                        'metadata' => [
                            'source_files' => $this->serializeSourceFiles([$sourceFile]),
                            'picking_output_mode' => $outputMode->value,
                            'error_message' => $message,
                        ],
                    ]);

                    $fileErrors[] = [
                        'source_name' => $sourceFile['original_name'],
                        'source_path' => $sourceFile['path'],
                        'message' => $message,
                    ];
                    $processingResults[] = [
                        'source_path' => $sourceFile['path'],
                        'status' => 'failed',
                        'error_message' => $message,
                    ];
                }
            }
        });

        if ($printJobIds === []) {
            $firstMessage = $fileErrors[0]['message'] ?? __('merchant.picking_list.errors.empty_spreadsheet');

            return new PdfNormalizationResult(
                implemented: true,
                success: false,
                metadata: [
                    'file_errors' => $fileErrors,
                    'picking_output_mode' => $outputMode->value,
                ],
                message: $firstMessage,
            );
        }

        $this->spreadsheetProcessingWriter->record(
            $context->uploadJobId,
            $sourceFiles,
            $processingResults,
        );

        return new PdfNormalizationResult(
            implemented: true,
            success: true,
            outputRelativePaths: $outputPaths,
            metadata: [
                'print_job_ids' => $printJobIds,
                'processed_pages' => count($printJobIds),
                'layout_mode' => 'picking_sheet',
                'row_count' => $totalRows,
                'total_units' => $totalUnits,
                'picking_output_mode' => $outputMode->value,
                'file_errors' => $fileErrors,
                'partial_success' => $fileErrors !== [],
                'failed_file_count' => count($fileErrors),
            ],
            message: $fileErrors !== []
                ? __('merchant.pdf.normalization.picking_list_partial_complete', [
                    'rows' => $totalRows,
                    'failed' => count($fileErrors),
                ])
                : __('merchant.pdf.normalization.picking_list_complete', [
                    'rows' => $totalRows,
                ]),
        );
    }

    /**
     * @param  array{original_name: string, disk: string, path: string, absolute_path: string}  $sourceFile
     * @return array{output_relative: string, print_job_id: int, row_count: int, total_units: int}
     */
    private function processSingleSourceFile(
        PdfProcessingContext $context,
        UploadJob $uploadJob,
        array $sourceFile,
        PickingOutputMode $outputMode,
        int $sheetNumber,
        Carbon $expiresAt,
    ): array {
        $rows = $this->parseRows([$sourceFile]);
        $document = $this->buildDocument($uploadJob, $rows, [$sourceFile]);
        $outputRelative = $this->buildOutputRelativePath($context, $sheetNumber);
        $outputAbsolute = $this->tempStorage->absolutePath(
            new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $outputRelative),
        );

        $this->pdfRenderer->renderToFile($document, $outputAbsolute);

        PickingList::query()->create([
            'merchant_id' => $context->merchantId,
            'country_code' => $context->countryCode,
            'upload_job_id' => $context->uploadJobId,
            'source_name' => $sourceFile['original_name'],
            'source_disk' => $sourceFile['disk'],
            'source_path' => $sourceFile['path'],
            'status' => UploadStatus::Completed,
            'row_count' => count($document->rows),
            'output_disk' => (string) config('pdf.temp_disk', 'temp'),
            'output_path' => $outputRelative,
            'metadata' => [
                'source_files' => $this->serializeSourceFiles([$sourceFile]),
                'document' => $document->toArray(),
                'picking_output_mode' => $outputMode->value,
            ],
        ]);

        $printJob = $this->createPrintJob(
            context: $context,
            sourceFiles: [$sourceFile],
            document: $document,
            outputRelative: $outputRelative,
            outputAbsolute: $outputAbsolute,
            expiresAt: $expiresAt,
            outputMode: $outputMode,
            sheetNumber: $sheetNumber,
        );

        return [
            'output_relative' => $outputRelative,
            'print_job_id' => $printJob->id,
            'row_count' => count($document->rows),
            'total_units' => $document->totalUnits,
        ];
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     */
    private function createPrintJob(
        PdfProcessingContext $context,
        array $sourceFiles,
        PickingListDocument $document,
        string $outputRelative,
        string $outputAbsolute,
        Carbon $expiresAt,
        PickingOutputMode $outputMode,
        int $sheetNumber,
    ): PrintJob {
        $firstSource = $sourceFiles[0];

        return PrintJob::query()->create([
            'upload_job_id' => $context->uploadJobId,
            'merchant_id' => $context->merchantId,
            'country_code' => $context->countryCode,
            'module' => 'picking_list',
            'status' => PrintJobStatus::Ready,
            'source_page_number' => $sheetNumber,
            'output_disk' => (string) config('pdf.temp_disk', 'temp'),
            'output_path' => $outputRelative,
            'output_width_mm' => 210.0,
            'output_height_mm' => 297.0,
            'checksum' => hash_file('sha256', $outputAbsolute) ?: null,
            'expires_at' => $expiresAt,
            'metadata' => [
                'layout_mode' => 'picking_sheet',
                'sheet_number' => $sheetNumber,
                'row_count' => count($document->rows),
                'total_units' => $document->totalUnits,
                'source_file_count' => count($sourceFiles),
                'source_file_name' => $firstSource['original_name'],
                'original_name' => $this->buildOutputFileName($sourceFiles),
                'picking_output_mode' => $outputMode->value,
                'document' => $document->toArray(),
                'source_files' => $this->serializeSourceFiles($sourceFiles),
            ],
        ]);
    }

    /**
     * @return list<array{original_name: string, disk: string, path: string, absolute_path: string}>
     */
    private function collectSpreadsheetSources(UploadJob $uploadJob): array
    {
        $metadata = $uploadJob->metadata ?? [];
        $spreadsheetFiles = is_array($metadata['spreadsheet_files'] ?? null)
            ? $metadata['spreadsheet_files']
            : [];

        if ($spreadsheetFiles === []) {
            throw PdfNormalizationException::failed('No spreadsheet sources found for picking list.');
        }

        $sources = [];

        foreach ($spreadsheetFiles as $file) {
            if (! is_array($file)) {
                continue;
            }

            $path = (string) ($file['path'] ?? '');
            $disk = (string) ($file['disk'] ?? 'temp');
            $originalName = (string) ($file['original_name'] ?? basename($path));

            if ($path === '') {
                continue;
            }

            $sources[] = [
                'original_name' => $originalName,
                'disk' => $disk,
                'path' => $path,
                'absolute_path' => $this->tempStorage->absolutePath(new PdfTempPath($disk, $path)),
            ];
        }

        if ($sources === []) {
            throw PdfNormalizationException::failed('No readable spreadsheet sources found for picking list.');
        }

        return $sources;
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     * @return list<array{original_name: string, disk: string, path: string}>
     */
    private function serializeSourceFiles(array $sourceFiles): array
    {
        return array_map(static fn (array $source): array => [
            'original_name' => $source['original_name'],
            'disk' => $source['disk'],
            'path' => $source['path'],
        ], $sourceFiles);
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     * @return list<PickingListRow>
     */
    private function parseRows(array $sourceFiles): array
    {
        $rows = [];
        $lineNumber = 0;

        foreach ($sourceFiles as $sourceFile) {
            $parsedRows = $this->spreadsheetReader->readFile(
                absolutePath: $sourceFile['absolute_path'],
                sourceFileName: $sourceFile['original_name'],
            );

            foreach ($parsedRows as $parsedRow) {
                $lineNumber++;
                $rows[] = new PickingListRow(
                    lineNumber: $lineNumber,
                    trackingNumber: $parsedRow->trackingNumber,
                    orderSn: $parsedRow->orderSn,
                    mainSku: $parsedRow->mainSku,
                    productName: $parsedRow->productName,
                    variantSku: $parsedRow->variantSku,
                    variantName: $parsedRow->variantName,
                    quantity: $parsedRow->quantity,
                    remarkFromBuyer: $parsedRow->remarkFromBuyer,
                    sellerNote: $parsedRow->sellerNote,
                    sourceFileName: $parsedRow->sourceFileName,
                    unitPrice: $parsedRow->unitPrice,
                );
            }
        }

        return $rows;
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     * @param  list<PickingListRow>  $rows
     */
    private function buildDocument(UploadJob $uploadJob, array $rows, array $sourceFiles): PickingListDocument
    {
        $accountName = $this->resolveAccountName($uploadJob);
        $generatedAt = now()->timezone(config('app.timezone', 'Asia/Singapore'))->format('h:i A d/m/Y');
        $totalUnits = array_sum(array_map(static fn (PickingListRow $row): int => $row->quantity, $rows));

        return new PickingListDocument(
            accountName: $accountName,
            generatedAt: $generatedAt,
            rows: $rows,
            sourceFiles: array_map(static fn (array $source): string => $source['original_name'], $sourceFiles),
            totalUnits: $totalUnits,
        );
    }

    private function resolveAccountName(UploadJob $uploadJob): string
    {
        /** @var User|null $uploadedBy */
        $uploadedBy = $uploadJob->uploadedBy;
        $merchantName = $uploadJob->merchant?->name;

        if ($uploadedBy?->name) {
            return (string) $uploadedBy->name;
        }

        if (is_string($merchantName) && $merchantName !== '') {
            return $merchantName;
        }

        return (string) config('app.name', 'XY Cubic Shopee');
    }

    /**
     * @param  list<array{original_name: string}>  $sourceFiles
     */
    private function buildOutputFileName(array $sourceFiles): string
    {
        if (count($sourceFiles) === 1) {
            $name = pathinfo($sourceFiles[0]['original_name'], PATHINFO_FILENAME);

            return "{$name}-picking-list.pdf";
        }

        return 'picking-list-merged.pdf';
    }

    private function buildOutputRelativePath(PdfProcessingContext $context, int $sheetNumber = 1): string
    {
        $base = $this->tempStorage->outputsDirectory($context->merchantId, $context->uploadJobId);

        return $base->relativePath.'/'.Str::uuid()->toString()."-picking-list-{$sheetNumber}.pdf";
    }
}
