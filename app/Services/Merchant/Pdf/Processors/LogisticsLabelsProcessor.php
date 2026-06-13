<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Processors;

use App\Contracts\Merchant\Pdf\PdfProcessorInterface;
use App\Contracts\Merchant\Pdf\ThermalPdfNormalizationInterface;
use App\Contracts\Merchant\Pdf\ThermalPdfValidationInterface;
use App\DTOs\Merchant\Pdf\PdfNormalizationResult;
use App\DTOs\Merchant\Pdf\PdfProcessingContext;
use App\DTOs\Merchant\Pdf\PdfTempPath;
use App\DTOs\Merchant\Pdf\ThermalA4OutputSpec;
use App\DTOs\Merchant\Pdf\ThermalPageMetrics;
use App\Enums\PdfProcessingMode;
use App\Enums\PdfValidationCode;
use App\Enums\PrintJobStatus;
use App\Enums\ThermalOutputMode;
use App\Exceptions\Merchant\Pdf\PdfNormalizationException;
use App\Exceptions\Merchant\Pdf\PdfValidationException;
use App\Models\PdfUpload;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Services\Merchant\Pdf\PdfBoundaryDetectionService;
use App\Services\Merchant\Pdf\PdfMergerService;
use App\Services\Merchant\Pdf\PdfTempStorageService;
use App\Services\Merchant\Pdf\ThermalA4SheetComposer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Normalizes uploaded thermal logistics labels onto A4 output sheets.
 */
class LogisticsLabelsProcessor implements PdfProcessorInterface
{
    public function __construct(
        private readonly ThermalPdfValidationInterface $thermalValidator,
        private readonly ThermalPdfNormalizationInterface $thermalNormalizer,
        private readonly ThermalA4SheetComposer $a4Composer,
        private readonly PdfBoundaryDetectionService $boundaryDetection,
        private readonly PdfTempStorageService $tempStorage,
        private readonly PdfMergerService $merger,
    ) {}

    public function supports(PdfProcessingMode $mode): bool
    {
        return $mode === PdfProcessingMode::ThermalLabel;
    }

    public function normalize(PdfProcessingContext $context): PdfNormalizationResult
    {
        $uploadJob = UploadJob::query()->findOrFail($context->uploadJobId);
        $outputMode = ThermalOutputMode::fromUploadMetadata(
            $uploadJob->metadata,
            $uploadJob->pdfUploads()->count(),
        );

        $sourcePages = $this->collectValidatedSourcePages($context);
        $preparedPages = $this->prepareLabelSlots($context, $sourcePages);
        $sheetGroups = $this->buildSheetGroups($preparedPages, $outputMode, ThermalA4OutputSpec::fromConfig());

        return $this->persistSheetGroups(
            context: $context,
            sheetGroups: $sheetGroups,
            outputMode: $outputMode,
            processedPageCount: count($sourcePages),
        );
    }

    public function regeneratePrintJob(PrintJob $printJob, PdfProcessingContext $context): PrintJob
    {
        $sourcePagesMeta = is_array($printJob->metadata['source_pages'] ?? null)
            ? $printJob->metadata['source_pages']
            : [];

        if ($sourcePagesMeta === []) {
            throw PdfNormalizationException::failed('Print job is missing source page metadata.');
        }

        $sourcePages = $this->resolveSourcePagesFromMetadata($context, $sourcePagesMeta);
        $preparedPages = $this->prepareLabelSlots($context, $sourcePages);

        if ($printJob->output_path !== null && Storage::disk($printJob->output_disk)->exists($printJob->output_path)) {
            Storage::disk($printJob->output_disk)->delete($printJob->output_path);
        }

        $a4Spec = ThermalA4OutputSpec::fromConfig();
        $sheetNumber = (int) ($printJob->metadata['sheet_number'] ?? $printJob->source_page_number);
        $outputRelative = $this->buildOutputRelativePath($context, $sheetNumber);
        $outputAbsolute = $this->tempStorage->absolutePath(
            new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $outputRelative),
        );

        $sheetGroups = array_chunk($preparedPages, $a4Spec->labelsPerPage);
        $sheetPaths = $this->composeSheetGroupsToPaths($sheetGroups, $context, $a4Spec);
        $this->finalizeMergedOutput($sheetPaths, $outputAbsolute);
        $allSheetSources = array_merge(...$sheetGroups);
        $layoutMode = count($allSheetSources) === 1 ? 'a4_single' : 'a4_multi';
        $firstSource = $allSheetSources[0];
        $metrics = $firstSource['metrics'];

        $printJob->update([
            'pdf_upload_id' => $firstSource['pdf_upload_id'],
            'status' => PrintJobStatus::Ready,
            'output_path' => $outputRelative,
            'source_width_mm' => $metrics->widthMm,
            'source_height_mm' => $metrics->heightMm,
            'source_orientation' => $metrics->orientation->value,
            'output_width_mm' => $a4Spec->pageWidthMm,
            'output_height_mm' => $a4Spec->pageHeightMm,
            'checksum' => hash_file('sha256', $outputAbsolute) ?: null,
            'expires_at' => now()->addMinutes((int) config('pdf.output_ttl_minutes', 10)),
            'error_message' => null,
            'metadata' => [
                ...($printJob->metadata ?? []),
                'layout_mode' => $layoutMode,
                'sheet_number' => $sheetNumber,
                'label_count' => count($allSheetSources),
                'page_count' => count($sheetGroups),
                'source_pages' => $this->serializeSourcePages($allSheetSources),
                'a4_spec' => $a4Spec->toArray(),
                'regenerated_at' => now()->toIso8601String(),
            ],
        ]);

        return $printJob->fresh();
    }

    /**
     * @param  list<array<string, mixed>>  $sourcePages
     * @return list<array<string, mixed>>
     */
    private function prepareLabelSlots(PdfProcessingContext $context, array $sourcePages): array
    {
        $workDirectory = $this->tempStorage->absolutePath(
            new PdfTempPath(
                (string) config('pdf.temp_disk', 'temp'),
                $this->tempStorage->workDirectory($context->merchantId, $context->uploadJobId)->relativePath,
            ),
        );

        if (! is_dir($workDirectory) && ! mkdir($workDirectory, 0755, true) && ! is_dir($workDirectory)) {
            throw PdfNormalizationException::failed('Could not create work directory.');
        }

        foreach ($sourcePages as $index => $sourcePage) {
            $slotRelative = $this->buildWorkRelativePath($context, $sourcePage['pdf_upload_id'], $sourcePage['page']);
            $slotAbsolute = $this->tempStorage->absolutePath(
                new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $slotRelative),
            );

            $normalization = $this->thermalNormalizer->renderLabelSlot(
                sourceAbsolutePath: $sourcePage['source_path'],
                pageNumber: $sourcePage['page'],
                context: $context,
                outputAbsolutePath: $slotAbsolute,
            );

            if (! $normalization->success) {
                throw PdfNormalizationException::failed('Label slot normalization failed.');
            }

            $sourcePages[$index]['slot_path'] = $slotAbsolute;
            $sourcePages[$index]['normalization'] = $normalization->metadata;
        }

        return $sourcePages;
    }

    /**
     * @param  list<array<string, mixed>>  $preparedPages
     * @return list<list<array<string, mixed>>>
     */
    private function buildSheetGroups(array $preparedPages, ThermalOutputMode $outputMode, ThermalA4OutputSpec $spec): array
    {
        if ($outputMode === ThermalOutputMode::Separate) {
            return $this->buildSeparateFileSheetGroups($preparedPages, $spec);
        }

        if (count($preparedPages) === 1) {
            return [array_slice($preparedPages, 0, 1)];
        }

        return array_chunk($preparedPages, $spec->labelsPerPage);
    }

    /**
     * @param  list<array<string, mixed>>  $preparedPages
     * @return list<list<array<string, mixed>>>
     */
    private function buildSeparateFileSheetGroups(array $preparedPages, ThermalA4OutputSpec $spec): array
    {
        $groups = [];
        $pagesByUpload = [];

        foreach ($preparedPages as $page) {
            $pagesByUpload[$page['pdf_upload_id']][] = $page;
        }

        foreach ($pagesByUpload as $pages) {
            if (count($pages) === 1) {
                $groups[] = [$pages[0]];

                continue;
            }

            foreach (array_chunk($pages, $spec->labelsPerPage) as $chunk) {
                $groups[] = $chunk;
            }
        }

        return $groups;
    }

    /**
     * @param  list<list<array<string, mixed>>>  $sheetGroups
     */
    private function persistSheetGroups(
        PdfProcessingContext $context,
        array $sheetGroups,
        ThermalOutputMode $outputMode,
        int $processedPageCount,
    ): PdfNormalizationResult {
        $outputPaths = [];
        $printJobIds = [];
        $a4Spec = ThermalA4OutputSpec::fromConfig();
        $expiresAt = now()->addMinutes((int) config('pdf.output_ttl_minutes', 10));
        $outputBundles = $this->buildOutputBundles($sheetGroups, $outputMode);

        DB::transaction(function () use (
            $outputBundles,
            $context,
            $outputMode,
            $a4Spec,
            $expiresAt,
            &$outputPaths,
            &$printJobIds,
        ): void {
            foreach ($outputBundles as $bundleIndex => $bundle) {
                $bundleSheetGroups = $bundle['sheet_groups'];
                $sheetNumber = $bundleIndex + 1;
                $printJob = $this->createPrintJobForBundle(
                    context: $context,
                    bundleSheetGroups: $bundleSheetGroups,
                    sheetNumber: $sheetNumber,
                    outputMode: $outputMode,
                    a4Spec: $a4Spec,
                    expiresAt: $expiresAt,
                );

                $outputPaths[] = (string) $printJob->output_path;
                $printJobIds[] = $printJob->id;
            }
        });

        $allLabels = array_sum(array_map(static fn (array $group): int => count($group), $sheetGroups));
        $primaryLayout = $allLabels === 1 ? 'a4_single' : 'a4_multi';

        return new PdfNormalizationResult(
            implemented: true,
            success: true,
            outputRelativePaths: $outputPaths,
            metadata: [
                'print_job_ids' => $printJobIds,
                'processed_pages' => $processedPageCount,
                'layout_mode' => $primaryLayout,
                'output_sheets' => count($sheetGroups),
                'thermal_output_mode' => $outputMode->value,
            ],
            message: __('merchant.pdf.normalization.logistics_complete', [
                'count' => $processedPageCount,
            ]),
        );
    }

    /**
     * @param  list<list<array<string, mixed>>>  $sheetGroups
     * @return list<array{sheet_groups: list<list<array<string, mixed>>>, pdf_upload_id: int|null}>
     */
    private function buildOutputBundles(array $sheetGroups, ThermalOutputMode $outputMode): array
    {
        if ($outputMode === ThermalOutputMode::Combined) {
            return [
                [
                    'sheet_groups' => $sheetGroups,
                    'pdf_upload_id' => $sheetGroups[0][0]['pdf_upload_id'] ?? null,
                ],
            ];
        }

        $bundles = [];
        $currentUploadId = null;
        $currentGroups = [];

        foreach ($sheetGroups as $sheetGroup) {
            $uploadId = (int) ($sheetGroup[0]['pdf_upload_id'] ?? 0);

            if ($currentUploadId !== null && $uploadId !== $currentUploadId) {
                $bundles[] = [
                    'sheet_groups' => $currentGroups,
                    'pdf_upload_id' => $currentUploadId,
                ];
                $currentGroups = [];
            }

            $currentUploadId = $uploadId;
            $currentGroups[] = $sheetGroup;
        }

        if ($currentGroups !== []) {
            $bundles[] = [
                'sheet_groups' => $currentGroups,
                'pdf_upload_id' => $currentUploadId,
            ];
        }

        return $bundles;
    }

    /**
     * @param  list<list<array<string, mixed>>>  $bundleSheetGroups
     */
    private function createPrintJobForBundle(
        PdfProcessingContext $context,
        array $bundleSheetGroups,
        int $sheetNumber,
        ThermalOutputMode $outputMode,
        ThermalA4OutputSpec $a4Spec,
        \Illuminate\Support\Carbon $expiresAt,
    ): PrintJob {
        $outputRelative = $this->buildOutputRelativePath($context, $sheetNumber);
        $outputAbsolute = $this->tempStorage->absolutePath(
            new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $outputRelative),
        );

        $sheetPaths = $this->composeSheetGroupsToPaths($bundleSheetGroups, $context, $a4Spec);
        $this->finalizeMergedOutput($sheetPaths, $outputAbsolute);

        $allSheetSources = array_merge(...$bundleSheetGroups);
        $layoutMode = count($allSheetSources) === 1 ? 'a4_single' : 'a4_multi';
        $firstSource = $allSheetSources[0];
        $metrics = $firstSource['metrics'];
        $sourceFileName = (string) $firstSource['original_name'];

        return PrintJob::query()->create([
            'upload_job_id' => $context->uploadJobId,
            'merchant_id' => $context->merchantId,
            'pdf_upload_id' => $firstSource['pdf_upload_id'],
            'country_code' => $context->countryCode,
            'module' => 'logistics_labels',
            'status' => PrintJobStatus::Ready,
            'source_page_number' => $sheetNumber,
            'output_disk' => (string) config('pdf.temp_disk', 'temp'),
            'output_path' => $outputRelative,
            'source_width_mm' => $metrics->widthMm,
            'source_height_mm' => $metrics->heightMm,
            'source_orientation' => $metrics->orientation->value,
            'output_width_mm' => $a4Spec->pageWidthMm,
            'output_height_mm' => $a4Spec->pageHeightMm,
            'checksum' => hash_file('sha256', $outputAbsolute) ?: null,
            'expires_at' => $expiresAt,
            'metadata' => [
                'layout_mode' => $layoutMode,
                'sheet_number' => $sheetNumber,
                'label_count' => count($allSheetSources),
                'page_count' => count($bundleSheetGroups),
                'thermal_output_mode' => $outputMode->value,
                'source_file_name' => $sourceFileName,
                'source_pages' => $this->serializeSourcePages($allSheetSources),
                'a4_spec' => $a4Spec->toArray(),
            ],
        ]);
    }

    /**
     * @param  list<list<array<string, mixed>>>  $sheetGroups
     * @return list<string>
     */
    private function composeSheetGroupsToPaths(
        array $sheetGroups,
        PdfProcessingContext $context,
        ThermalA4OutputSpec $a4Spec,
    ): array {
        $sheetPaths = [];

        foreach ($sheetGroups as $sheetIndex => $sheetSources) {
            $tempRelative = $this->buildWorkRelativePath(
                $context,
                (int) ($sheetSources[0]['pdf_upload_id'] ?? 0),
                (int) ($sheetSources[0]['page'] ?? 0),
            ).'-sheet'.($sheetIndex + 1).'.pdf';
            $tempAbsolute = $this->tempStorage->absolutePath(
                new PdfTempPath((string) config('pdf.temp_disk', 'temp'), $tempRelative),
            );

            $this->composeSheet($sheetSources, $tempAbsolute, $a4Spec);
            $sheetPaths[] = $tempAbsolute;
        }

        return $sheetPaths;
    }

    /**
     * @param  list<string>  $sheetPaths
     */
    private function finalizeMergedOutput(array $sheetPaths, string $outputAbsolute): void
    {
        if ($sheetPaths === []) {
            throw PdfNormalizationException::failed('No thermal sheets were composed.');
        }

        if (count($sheetPaths) === 1) {
            $this->ensureOutputDirectory($outputAbsolute);

            if (! rename($sheetPaths[0], $outputAbsolute)) {
                if (! copy($sheetPaths[0], $outputAbsolute)) {
                    throw PdfNormalizationException::failed('Could not finalize thermal output file.');
                }

                @unlink($sheetPaths[0]);
            }

            return;
        }

        $this->merger->mergeToFile($sheetPaths, $outputAbsolute);

        foreach ($sheetPaths as $sheetPath) {
            if (is_file($sheetPath)) {
                @unlink($sheetPath);
            }
        }
    }

    private function ensureOutputDirectory(string $outputAbsolutePath): void
    {
        $directory = dirname($outputAbsolutePath);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw PdfNormalizationException::failed('Could not create output directory.');
        }
    }

    /**
     * @param  list<array<string, mixed>>  $sheetSources
     * @return array<string, mixed>
     */
    private function composeSheet(array $sheetSources, string $outputAbsolute, ThermalA4OutputSpec $a4Spec): array
    {
        $slotPathsForSheet = array_values(array_map(
            static fn (array $source): string => (string) $source['slot_path'],
            $sheetSources,
        ));

        if (count($sheetSources) === 1) {
            return $this->a4Composer->composeSingle($slotPathsForSheet[0], $outputAbsolute, $a4Spec);
        }

        return $this->a4Composer->composeMulti($slotPathsForSheet, $outputAbsolute, $a4Spec);
    }

    /**
     * @param  list<array<string, mixed>>  $sheetSources
     * @return list<array<string, mixed>>
     */
    private function serializeSourcePages(array $sheetSources): array
    {
        return array_map(static fn (array $source): array => [
            'pdf_upload_id' => $source['pdf_upload_id'],
            'page' => $source['page'],
            'original_name' => $source['original_name'],
            'metrics' => $source['metrics']->toArray(),
            'normalization' => $source['normalization'] ?? [],
        ], $sheetSources);
    }

    /**
     * @param  list<array<string, mixed>>  $sourcePagesMeta
     * @return list<array<string, mixed>>
     */
    private function resolveSourcePagesFromMetadata(PdfProcessingContext $context, array $sourcePagesMeta): array
    {
        $uploadIds = collect($sourcePagesMeta)
            ->pluck('pdf_upload_id')
            ->filter()
            ->unique()
            ->all();

        /** @var Collection<int, PdfUpload> $uploads */
        $uploads = PdfUpload::query()
            ->where('upload_job_id', $context->uploadJobId)
            ->whereIn('id', $uploadIds)
            ->get()
            ->keyBy('id');

        $sourcePages = [];

        foreach ($sourcePagesMeta as $meta) {
            $pdfUploadId = (int) ($meta['pdf_upload_id'] ?? 0);
            $page = (int) ($meta['page'] ?? 0);
            $pdfUpload = $uploads->get($pdfUploadId);

            if ($pdfUpload === null || $page <= 0) {
                throw PdfNormalizationException::failed('Could not resolve source file for regeneration.');
            }

            $sourcePath = $this->tempStorage->absolutePath(
                new PdfTempPath($pdfUpload->disk, $pdfUpload->path),
            );

            $boundary = $this->boundaryDetection->detectFromFile($sourcePath, $page);
            $validation = $this->thermalValidator->validateBoundary($boundary);

            if (! $validation->passed) {
                throw new PdfValidationException(
                    $validation->codes[0] ?? PdfValidationCode::InvalidPdf,
                    $validation->messages[0] ?? null,
                );
            }

            $sourcePages[] = [
                'pdf_upload_id' => $pdfUploadId,
                'page' => $page,
                'source_path' => $sourcePath,
                'original_name' => $pdfUpload->original_name,
                'metrics' => $this->thermalValidator->analyzeBoundary($boundary),
            ];
        }

        return $sourcePages;
    }

    /**
     * @return list<array{
     *     pdf_upload_id: int,
     *     page: int,
     *     source_path: string,
     *     original_name: string,
     *     metrics: ThermalPageMetrics
     * }>
     */
    private function collectValidatedSourcePages(PdfProcessingContext $context): array
    {
        $pdfUploads = PdfUpload::query()
            ->where('upload_job_id', $context->uploadJobId)
            ->orderBy('id')
            ->get();

        if ($pdfUploads->isEmpty()) {
            throw PdfNormalizationException::failed('No PDF sources found for logistics labels.');
        }

        $sourcePages = [];

        foreach ($pdfUploads as $pdfUpload) {
            $sourcePath = $this->tempStorage->absolutePath(
                new PdfTempPath($pdfUpload->disk, $pdfUpload->path),
            );

            $pageCount = $this->boundaryDetection->pageCount($sourcePath);

            for ($page = 1; $page <= $pageCount; $page++) {
                $boundary = $this->boundaryDetection->detectFromFile($sourcePath, $page);
                $validation = $this->thermalValidator->validateBoundary($boundary);

                if (! $validation->passed) {
                    throw new PdfValidationException(
                        $validation->codes[0] ?? PdfValidationCode::InvalidPdf,
                        $validation->messages[0] ?? null,
                    );
                }

                $sourcePages[] = [
                    'pdf_upload_id' => $pdfUpload->id,
                    'page' => $page,
                    'source_path' => $sourcePath,
                    'original_name' => $pdfUpload->original_name,
                    'metrics' => $this->thermalValidator->analyzeBoundary($boundary),
                ];
            }
        }

        if ($sourcePages === []) {
            throw PdfNormalizationException::failed('No valid thermal pages found.');
        }

        return $sourcePages;
    }

    private function buildOutputRelativePath(PdfProcessingContext $context, int $sheetNumber): string
    {
        $base = $this->tempStorage->outputsDirectory($context->merchantId, $context->uploadJobId);

        return $base->relativePath.'/'.Str::uuid()->toString()."-sheet{$sheetNumber}.pdf";
    }

    private function buildWorkRelativePath(PdfProcessingContext $context, int $pdfUploadId, int $page): string
    {
        $base = $this->tempStorage->workDirectory($context->merchantId, $context->uploadJobId);

        return $base->relativePath.'/'.Str::uuid()->toString()."-{$pdfUploadId}-p{$page}-slot.pdf";
    }
}
