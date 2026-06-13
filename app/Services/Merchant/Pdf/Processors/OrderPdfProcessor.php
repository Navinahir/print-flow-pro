<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Processors;

use App\Contracts\Merchant\Pdf\PdfProcessorInterface;
use App\DTOs\Merchant\OrderPdf\OrderPdfDocument;
use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfTempPath;
use App\DTOs\Merchant\PickingList\PickingListRow;
use App\Enums\OrderOutputMode;
use App\Enums\PdfProcessingMode;
use App\Enums\PrintJobStatus;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Services\Merchant\OrderPdf\OrderPdfDocumentBuilder;
use App\Services\Merchant\OrderPdf\OrderPdfRenderer;
use App\Services\Merchant\Pdf\PdfTempStorageService;
use App\Services\Merchant\Pdf\ShopeeOrderPdfParser;
use App\Services\Merchant\PickingList\PickingListSpreadsheetReader;
use App\Services\Merchant\Spreadsheet\SpreadsheetBatchRowParser;
use App\Services\Merchant\Spreadsheet\SpreadsheetProcessingMetadataWriter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class OrderPdfProcessor implements PdfProcessorInterface
{
    public function __construct(
        private readonly PickingListSpreadsheetReader $spreadsheetReader,
        private readonly OrderPdfDocumentBuilder $documentBuilder,
        private readonly OrderPdfRenderer $pdfRenderer,
        private readonly ShopeeOrderPdfParser $orderPdfParser,
        private readonly PdfTempStorageService $tempStorage,
        private readonly SpreadsheetBatchRowParser $spreadsheetBatchRowParser,
        private readonly SpreadsheetProcessingMetadataWriter $spreadsheetProcessingWriter,
    ) {}

    public function supports(PdfProcessingMode $mode): bool
    {
        return $mode === PdfProcessingMode::OrderPdfMerge;
    }

    public function normalize(PdfProcessingContext $context): PdfNormalizationResult
    {
        $uploadJob = UploadJob::query()->findOrFail($context->uploadJobId);
        $sourceFiles = $this->collectSpreadsheetSources($uploadJob);
        $outputMode = OrderOutputMode::fromUploadMetadata($uploadJob->metadata, count($sourceFiles));

        if ($outputMode === OrderOutputMode::Separate && count($sourceFiles) > 1) {
            return $this->normalizeSeparate($context, $sourceFiles, $outputMode);
        }

        return $this->normalizeCombined($context, $sourceFiles, $outputMode);
    }

    public function regeneratePrintJob(PrintJob $printJob, PdfProcessingContext $context): PrintJob
    {
        $sourceFiles = $this->resolveSourceFilesFromMetadata($printJob);

        if (is_string($printJob->output_path) && $printJob->output_path !== '') {
            Storage::disk($printJob->output_disk)->delete($printJob->output_path);
        }

        $rows = $this->parseRows($sourceFiles);
        $document = $this->buildDocument($rows, $sourceFiles);
        $sheetNumber = (int) ($printJob->metadata['sheet_number'] ?? $printJob->source_page_number);
        $outputRelative = $this->buildOutputRelativePath($context, $sheetNumber);
        $outputAbsolute = $this->tempStorage->absolutePath(
            new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $outputRelative),
        );

        $pageCount = $this->pdfRenderer->renderToFile($document, $outputAbsolute);
        $outputModeValue = (string) ($printJob->metadata['order_output_mode'] ?? OrderOutputMode::Combined->value);
        $outputMode = OrderOutputMode::tryFrom($outputModeValue) ?? OrderOutputMode::Combined;
        $parsedContent = $this->orderPdfParser->parseFile($outputAbsolute)->toArray();

        $printJob->update([
            'status' => PrintJobStatus::Ready,
            'output_path' => $outputRelative,
            'checksum' => hash_file('sha256', $outputAbsolute) ?: null,
            'expires_at' => now()->addMinutes((int) config('pdf.output_ttl_minutes', 10)),
            'error_message' => null,
            'metadata' => [
                ...($printJob->metadata ?? []),
                'layout_mode' => 'generated',
                'sheet_number' => $sheetNumber,
                'page_count' => $pageCount,
                'order_count' => count($document->orders),
                'source_file_count' => count($sourceFiles),
                'source_file_name' => $sourceFiles[0]['original_name'],
                'original_name' => $this->buildOutputFileName($sourceFiles),
                'order_output_mode' => $outputMode->value,
                'document' => $document->toArray(),
                'source_files' => $this->serializeSourceFiles($sourceFiles),
                'parsed_content' => $parsedContent,
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
        array $sourceFiles,
        OrderOutputMode $outputMode,
    ): PdfNormalizationResult {
        $batch = $this->spreadsheetBatchRowParser->parse(
            $sourceFiles,
            'merchant.order_pdf.errors.invalid_format',
        );

        if ($batch->successfulFiles === []) {
            $firstMessage = $batch->fileErrors[0]['message'] ?? __('merchant.order_pdf.errors.empty_spreadsheet');

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
                    'order_output_mode' => $outputMode->value,
                ],
                message: $firstMessage,
            );
        }

        $document = $this->buildDocument($batch->rows, $batch->successfulFiles);
        $outputRelative = $this->buildOutputRelativePath($context);
        $outputAbsolute = $this->tempStorage->absolutePath(
            new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $outputRelative),
        );

        $pageCount = $this->pdfRenderer->renderToFile($document, $outputAbsolute);
        $expiresAt = now()->addMinutes((int) config('pdf.output_ttl_minutes', 10));

        $printJob = DB::transaction(function () use (
            $context,
            $batch,
            $document,
            $outputRelative,
            $outputAbsolute,
            $expiresAt,
            $outputMode,
            $pageCount,
        ): PrintJob {
            return $this->createPrintJob(
                context: $context,
                sourceFiles: $batch->successfulFiles,
                document: $document,
                outputRelative: $outputRelative,
                outputAbsolute: $outputAbsolute,
                expiresAt: $expiresAt,
                outputMode: $outputMode,
                sheetNumber: 1,
                pageCount: $pageCount,
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
                'processed_pages' => $pageCount,
                'layout_mode' => 'generated',
                'order_count' => count($document->orders),
                'source_file_count' => count($batch->successfulFiles),
                'order_output_mode' => $outputMode->value,
                'file_errors' => $batch->fileErrors,
                'partial_success' => $batch->fileErrors !== [],
                'failed_file_count' => count($batch->fileErrors),
            ],
            message: $batch->fileErrors !== []
                ? __('merchant.pdf.normalization.order_partial_complete', [
                    'orders' => count($document->orders),
                    'failed' => count($batch->fileErrors),
                ])
                : __('merchant.pdf.normalization.order_merge_complete', [
                    'pages' => count($document->orders),
                    'files' => count($batch->successfulFiles),
                ]),
        );
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     */
    private function normalizeSeparate(
        PdfProcessingContext $context,
        array $sourceFiles,
        OrderOutputMode $outputMode,
    ): PdfNormalizationResult {
        $outputPaths = [];
        $printJobIds = [];
        $fileErrors = [];
        $totalOrders = 0;
        $expiresAt = now()->addMinutes((int) config('pdf.output_ttl_minutes', 10));
        $processingResults = [];

        DB::transaction(function () use (
            $context,
            $sourceFiles,
            $outputMode,
            $expiresAt,
            &$outputPaths,
            &$printJobIds,
            &$fileErrors,
            &$totalOrders,
            &$processingResults,
        ): void {
            foreach ($sourceFiles as $index => $sourceFile) {
                $sheetNumber = $index + 1;

                try {
                    $result = $this->processSingleSourceFile(
                        context: $context,
                        sourceFile: $sourceFile,
                        outputMode: $outputMode,
                        sheetNumber: $sheetNumber,
                        expiresAt: $expiresAt,
                    );

                    $outputPaths[] = $result['output_relative'];
                    $printJobIds[] = $result['print_job_id'];
                    $totalOrders += $result['order_count'];
                    $processingResults[] = [
                        'source_path' => $sourceFile['path'],
                        'status' => 'completed',
                        'error_message' => null,
                    ];
                } catch (Throwable $exception) {
                    $message = $exception instanceof PdfNormalizationException
                        ? $exception->getMessage()
                        : __('merchant.order_pdf.errors.invalid_format', ['detail' => $exception->getMessage()]);

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

        $this->spreadsheetProcessingWriter->record($context->uploadJobId, $sourceFiles, $processingResults);

        if ($printJobIds === []) {
            $firstMessage = $fileErrors[0]['message'] ?? __('merchant.order_pdf.errors.empty_spreadsheet');

            return new PdfNormalizationResult(
                implemented: true,
                success: false,
                metadata: [
                    'file_errors' => $fileErrors,
                    'order_output_mode' => $outputMode->value,
                ],
                message: $firstMessage,
            );
        }

        return new PdfNormalizationResult(
            implemented: true,
            success: true,
            outputRelativePaths: $outputPaths,
            metadata: [
                'print_job_ids' => $printJobIds,
                'processed_pages' => $totalOrders,
                'layout_mode' => 'generated',
                'order_count' => $totalOrders,
                'source_file_count' => count($sourceFiles),
                'order_output_mode' => $outputMode->value,
                'file_errors' => $fileErrors,
                'partial_success' => $fileErrors !== [],
                'failed_file_count' => count($fileErrors),
            ],
            message: $fileErrors !== []
                ? __('merchant.pdf.normalization.order_partial_complete', [
                    'orders' => $totalOrders,
                    'failed' => count($fileErrors),
                ])
                : __('merchant.pdf.normalization.order_merge_complete', [
                    'pages' => $totalOrders,
                    'files' => count($sourceFiles),
                ]),
        );
    }

    /**
     * @param  array{original_name: string, disk: string, path: string, absolute_path: string}  $sourceFile
     * @return array{output_relative: string, print_job_id: int, order_count: int}
     */
    private function processSingleSourceFile(
        PdfProcessingContext $context,
        array $sourceFile,
        OrderOutputMode $outputMode,
        int $sheetNumber,
        Carbon $expiresAt,
    ): array {
        $rows = $this->parseRows([$sourceFile]);
        $document = $this->buildDocument($rows, [$sourceFile]);
        $outputRelative = $this->buildOutputRelativePath($context, $sheetNumber);
        $outputAbsolute = $this->tempStorage->absolutePath(
            new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $outputRelative),
        );

        $pageCount = $this->pdfRenderer->renderToFile($document, $outputAbsolute);

        $printJob = $this->createPrintJob(
            context: $context,
            sourceFiles: [$sourceFile],
            document: $document,
            outputRelative: $outputRelative,
            outputAbsolute: $outputAbsolute,
            expiresAt: $expiresAt,
            outputMode: $outputMode,
            sheetNumber: $sheetNumber,
            pageCount: $pageCount,
        );

        return [
            'output_relative' => $outputRelative,
            'print_job_id' => $printJob->id,
            'order_count' => count($document->orders),
        ];
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     */
    private function createPrintJob(
        PdfProcessingContext $context,
        array $sourceFiles,
        OrderPdfDocument $document,
        string $outputRelative,
        string $outputAbsolute,
        Carbon $expiresAt,
        OrderOutputMode $outputMode,
        int $sheetNumber,
        int $pageCount,
    ): PrintJob {
        $firstSource = $sourceFiles[0];
        $parsedContent = $this->orderPdfParser->parseFile($outputAbsolute)->toArray();

        return PrintJob::query()->create([
            'upload_job_id' => $context->uploadJobId,
            'merchant_id' => $context->merchantId,
            'country_code' => $context->countryCode,
            'module' => 'order_details',
            'status' => PrintJobStatus::Ready,
            'source_page_number' => $sheetNumber,
            'output_disk' => (string) config('pdf.temp_disk', 'temp'),
            'output_path' => $outputRelative,
            'output_width_mm' => 210.0,
            'output_height_mm' => 297.0,
            'checksum' => hash_file('sha256', $outputAbsolute) ?: null,
            'expires_at' => $expiresAt,
            'metadata' => [
                'layout_mode' => 'generated',
                'sheet_number' => $sheetNumber,
                'page_count' => $pageCount,
                'order_count' => count($document->orders),
                'source_file_count' => count($sourceFiles),
                'source_file_name' => $firstSource['original_name'],
                'original_name' => $this->buildOutputFileName($sourceFiles),
                'order_output_mode' => $outputMode->value,
                'document' => $document->toArray(),
                'source_files' => $this->serializeSourceFiles($sourceFiles),
                'parsed_content' => $parsedContent,
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
            throw PdfNormalizationException::failed('No spreadsheet sources found for order PDF.');
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
            throw PdfNormalizationException::failed('No readable spreadsheet sources found for order PDF.');
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
                $rows[] = $parsedRow;
            }
        }

        return $rows;
    }

    /**
     * @param  list<array{original_name: string, disk: string, path: string, absolute_path: string}>  $sourceFiles
     * @param  list<PickingListRow>  $rows
     */
    private function buildDocument(array $rows, array $sourceFiles): OrderPdfDocument
    {
        $document = $this->documentBuilder->build(
            $rows,
            array_map(static fn (array $source): string => $source['original_name'], $sourceFiles),
        );

        if ($document->orders === []) {
            throw PdfNormalizationException::failed(__('merchant.order_pdf.errors.empty_spreadsheet'));
        }

        return $document;
    }

    /**
     * @param  list<array{original_name: string}>  $sourceFiles
     */
    private function buildOutputFileName(array $sourceFiles): string
    {
        if (count($sourceFiles) === 1) {
            $name = pathinfo($sourceFiles[0]['original_name'], PATHINFO_FILENAME);

            return "{$name}-order.pdf";
        }

        return 'order-pdf-merged.pdf';
    }

    private function buildOutputRelativePath(PdfProcessingContext $context, int $sheetNumber = 1): string
    {
        $base = $this->tempStorage->outputsDirectory($context->merchantId, $context->uploadJobId);

        return $base->relativePath.'/'.Str::uuid()->toString()."-order-{$sheetNumber}.pdf";
    }
}
