<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\DTOs\Merchant\Preview\PreviewConfiguration;
use App\DTOs\Merchant\Upload\UploadPreviewResult;
use App\Enums\OrderOutputMode;
use App\Enums\PickingOutputMode;
use App\Enums\PrintJobStatus;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Services\Merchant\Preview\PreviewConfigurationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Prepares structured view data for the merchant upload detail page.
 */
class UploadShowViewService
{
    public function __construct(
        private readonly PreviewConfigurationService $previewConfigurationService,
    ) {}

    /**
     * @return array{
     *     is_thermal: bool,
     *     use_pdf_preview: bool,
     *     preview_config: PreviewConfiguration,
     *     summary: array<string, mixed>,
     *     thermal_summary: array<string, mixed>|null,
     *     print_outputs: list<array<string, mixed>>,
     *     source_files: list<array<string, mixed>>,
     *     thermal_output_mode: string
     * }
     */
    public function prepare(UploadJob $job, UploadPreviewResult $uploadPreview): array
    {
        $isThermal = $job->type === UploadJobType::ThermalLabel;
        $isOrderPdf = $job->type === UploadJobType::OrderPdf;
        $isPickingList = $job->type === UploadJobType::PickingList;
        $printJobs = $job->printJobs->sortBy('id')->values();
        $usePdfPreview = ($isThermal || $isOrderPdf || $isPickingList)
            && $this->hasSuccessfulOutputs($job->status)
            && $printJobs->isNotEmpty();

        return [
            'upload_type' => $job->type?->value,
            'is_thermal' => $isThermal,
            'is_order_pdf' => $isOrderPdf,
            'is_picking_list' => $isPickingList,
            'is_delivery_label' => $job->type === UploadJobType::DeliveryLabel,
            'use_pdf_preview' => $usePdfPreview,
            'preview_config' => $this->resolvePreviewConfiguration($job, $usePdfPreview),
            'summary' => $this->buildSummary($job),
            'thermal_summary' => $isThermal ? $this->buildThermalSummary($job, $printJobs) : null,
            'order_summary' => $isOrderPdf ? $this->buildOrderSummary($job, $printJobs) : null,
            'picking_summary' => $isPickingList ? $this->buildPickingSummary($job, $printJobs) : null,
            'print_outputs' => $this->buildPrintOutputs($job, $printJobs),
            'source_files' => $this->buildSourceFiles($job),
            'failed_source_files' => in_array($job->type, [UploadJobType::PickingList, UploadJobType::OrderPdf], true)
                ? $this->buildFailedSourceFiles($job)
                : [],
            'source_files_heading' => $this->resolveSourceFilesHeading($job),
            'source_files_hint' => $this->resolveSourceFilesHint($job),
            'print_outputs_hint' => $this->resolvePrintOutputsHint($job),
            'thermal_output_mode' => \App\Enums\ThermalOutputMode::fromUploadMetadata(
                $job->metadata,
                $job->pdfUploads->count(),
            )->value,
            'picking_output_mode' => PickingOutputMode::fromUploadMetadata(
                $job->metadata,
                count($job->metadata['spreadsheet_files'] ?? []),
            )->value,
            'order_output_mode' => OrderOutputMode::fromUploadMetadata(
                $job->metadata,
                count($job->metadata['spreadsheet_files'] ?? []),
            )->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSummary(UploadJob $job): array
    {
        return [
            'type_label' => $job->type?->label() ?? __('merchant.general.not_available'),
            'status' => $job->status,
            'uploaded_by' => $job->uploadedBy?->name ?? __('merchant.general.not_available'),
            'file_count' => $job->file_count,
            'created_at' => $job->created_at?->format('Y-m-d H:i'),
            'updated_at' => $job->updated_at?->format('Y-m-d H:i'),
        ];
    }

    /**
     * @param  Collection<int, PrintJob>  $printJobs
     * @return array<string, mixed>|null
     */
    private function buildThermalSummary(UploadJob $job, Collection $printJobs): ?array
    {
        if (! $this->hasSuccessfulOutputs($job->status) || $printJobs->isEmpty()) {
            return null;
        }

        $totalLabels = 0;
        $totalPages = 0;

        foreach ($printJobs as $printJob) {
            $totalLabels += (int) ($printJob->metadata['label_count'] ?? 1);
            $totalPages += (int) ($printJob->metadata['page_count'] ?? 1);
        }

        $hasMultiLayout = $printJobs->contains(
            static fn (PrintJob $printJob): bool => ($printJob->metadata['layout_mode'] ?? '') === 'a4_multi',
        );

        $outputFiles = $printJobs->count();

        return [
            'source_files' => $job->pdfUploads->count(),
            'total_labels' => $totalLabels,
            'a4_sheets' => $totalPages,
            'output_files' => $outputFiles,
            'merged_single_pdf' => $outputFiles === 1,
            'layout_key' => $hasMultiLayout ? 'multi' : 'single',
        ];
    }

    /**
     * @param  Collection<int, PrintJob>  $printJobs
     * @return array<string, mixed>|null
     */
    private function buildOrderSummary(UploadJob $job, Collection $printJobs): ?array
    {
        if (! $this->hasSuccessfulOutputs($job->status) || $printJobs->isEmpty()) {
            return null;
        }

        $totalPages = 0;
        $totalOrders = 0;

        foreach ($printJobs as $printJob) {
            $totalPages += (int) ($printJob->metadata['page_count'] ?? 0);
            $totalOrders += (int) ($printJob->metadata['order_count'] ?? 0);
        }

        $spreadsheetFiles = is_array($job->metadata['spreadsheet_files'] ?? null)
            ? $job->metadata['spreadsheet_files']
            : [];
        $failedFiles = count(array_filter(
            is_array($job->metadata['spreadsheet_processing'] ?? null) ? $job->metadata['spreadsheet_processing'] : [],
            static fn (array $entry): bool => ($entry['status'] ?? null) === UploadStatus::Failed->value
                || ($entry['status'] ?? null) === 'failed',
        ));

        return [
            'source_files' => count($spreadsheetFiles),
            'total_pages' => $totalPages > 0 ? $totalPages : $totalOrders,
            'merged_documents' => $printJobs->count(),
            'order_count' => $totalOrders,
            'failed_files' => $failedFiles,
            'has_partial_errors' => $failedFiles > 0,
        ];
    }

    /**
     * @param  Collection<int, PrintJob>  $printJobs
     * @return array<string, mixed>|null
     */
    private function buildPickingSummary(UploadJob $job, Collection $printJobs): ?array
    {
        if (! $this->hasSuccessfulOutputs($job->status)) {
            return null;
        }

        $rowCount = 0;
        $totalUnits = 0;
        $failedFiles = 0;

        foreach ($printJobs as $printJob) {
            $rowCount += (int) ($printJob->metadata['row_count'] ?? 0);
            $totalUnits += (int) ($printJob->metadata['total_units'] ?? 0);
        }

        foreach ($job->pickingLists as $pickingList) {
            if ($pickingList->status === UploadStatus::Failed) {
                $failedFiles++;
            }
        }

        $spreadsheetFiles = is_array($job->metadata['spreadsheet_files'] ?? null)
            ? $job->metadata['spreadsheet_files']
            : [];

        return [
            'row_count' => $rowCount,
            'total_units' => $totalUnits,
            'source_files' => count($spreadsheetFiles),
            'output_documents' => $printJobs->count(),
            'failed_files' => $failedFiles,
            'has_partial_errors' => $failedFiles > 0,
        ];
    }

    private function buildPrintOutputs(UploadJob $job, Collection $printJobs): array
    {
        if (! in_array($job->type, [UploadJobType::ThermalLabel, UploadJobType::OrderPdf, UploadJobType::PickingList], true) || $printJobs->isEmpty()) {
            return [];
        }

        return $printJobs
            ->map(fn (PrintJob $printJob): array => $this->formatPrintOutput(
                $printJob,
                $job,
                $printJobs,
            ))
            ->all();
    }

    /**
     * @param  Collection<int, PrintJob>|null  $allPrintJobs
     * @return array<string, mixed>
     */
    public function formatPrintOutput(
        PrintJob $printJob,
        ?UploadJob $uploadJob = null,
        ?Collection $allPrintJobs = null,
    ): array {
        $module = (string) ($printJob->module ?? 'logistics_labels');
        $isOrderMerge = $module === 'order_details';
        $isPickingSheet = $module === 'picking_list';
        $sheetNumber = (int) ($printJob->metadata['sheet_number'] ?? $printJob->source_page_number);
        $labelCount = (int) ($printJob->metadata['label_count'] ?? 1);
        $pageCount = (int) ($printJob->metadata['page_count'] ?? 0);
        $layoutMode = (string) ($printJob->metadata['layout_mode'] ?? 'a4_single');
        $sourcePages = is_array($printJob->metadata['source_pages'] ?? null)
            ? $printJob->metadata['source_pages']
            : [];

        $canDownload = in_array($printJob->status, [PrintJobStatus::Ready, PrintJobStatus::Downloaded], true);
        $fileExists = is_string($printJob->output_path)
            && $printJob->output_path !== ''
            && Storage::disk($printJob->output_disk)->exists($printJob->output_path);
        $canPreview = $canDownload && $fileExists;
        $canDownloadFile = $canPreview
            && ($printJob->expires_at === null || $printJob->expires_at->isFuture());
        $uploadStatus = $uploadJob?->status;
        $isThermal = $module === 'logistics_labels';
        $canRegenerate = $canPreview
            && $uploadStatus !== null
            && $this->hasSuccessfulOutputs($uploadStatus)
            && $this->canRegeneratePrintJob($printJob, $isThermal, $sourcePages);

        $physicalPages = $pageCount > 0 ? $pageCount : 1;
        $sourceGroups = $this->groupSourcePagesByFile($sourcePages);

        $layoutLabel = $isOrderMerge
            ? (string) __('merchant.uploads.detail.layout_merge', ['count' => $pageCount])
            : ($isPickingSheet
                ? (string) __('merchant.uploads.detail.layout_picking', ['count' => (int) ($printJob->metadata['row_count'] ?? 0)])
                : ($isThermal
                    ? $this->formatThermalLayoutLabel($labelCount, $physicalPages, $layoutMode)
                    : ($layoutMode === 'a4_multi'
                        ? (string) __('merchant.uploads.detail.layout_multi', ['count' => min($labelCount, 4)])
                        : (string) __('merchant.uploads.detail.layout_single'))));

        return [
            'id' => $printJob->id,
            'list_id' => 'print-job-'.$printJob->id,
            'sheet_number' => $sheetNumber,
            'title' => $this->resolveOutputTitle($printJob, $sheetNumber, $sourcePages, $allPrintJobs, $isOrderMerge),
            'layout_mode' => $layoutMode,
            'layout_label' => $layoutLabel,
            'size_label' => $isOrderMerge
                ? (string) __('merchant.uploads.detail.merge_size_label', ['count' => $pageCount])
                : ($isPickingSheet
                    ? (string) __('merchant.uploads.detail.picking_size_label', ['count' => (int) ($printJob->metadata['row_count'] ?? 0)])
                    : (string) __('merchant.uploads.detail.a4_size_label', [
                        'width' => (int) $printJob->output_width_mm,
                        'height' => (int) $printJob->output_height_mm,
                    ])),
            'label_count' => $labelCount,
            'page_count' => $physicalPages,
            'row_count' => (int) ($printJob->metadata['row_count'] ?? 0),
            'order_count' => (int) ($printJob->metadata['order_count'] ?? $pageCount),
            'output_kind' => $isThermal ? 'thermal' : ($isPickingSheet ? 'picking' : ($isOrderMerge ? 'order' : 'other')),
            'status_label' => $printJob->status->label(),
            'status_value' => $printJob->status->value,
            'source_summary' => $this->summarizeSourcePages($sourcePages),
            'source_groups' => $sourceGroups,
            'source_heading' => $isOrderMerge
                ? (string) __('merchant.uploads.detail.source_pages_heading')
                : ($isPickingSheet
                    ? (string) __('merchant.uploads.detail.source_spreadsheets_heading')
                    : (string) __('merchant.uploads.detail.source_labels_heading')),
            'source_pages' => $sourcePages,
            'download_url' => $canDownloadFile
                ? $this->downloadRouteForModule($module, $printJob)
                : null,
            'preview_url' => $canPreview
                ? $this->previewRouteForModule($module, $printJob)
                : null,
            'regenerate_url' => $canRegenerate
                ? route('uploads.print_jobs.regenerate', [$printJob->upload_job_id, $printJob])
                : null,
            'can_regenerate' => $canRegenerate,
        ];
    }

    private function downloadRouteForModule(string $module, PrintJob $printJob): string
    {
        return match ($module) {
            'order_details' => route('printing.order_details.download', $printJob),
            'picking_list' => route('printing.picking_list.download', $printJob),
            default => route('printing.logistics_labels.download', $printJob),
        };
    }

    private function previewRouteForModule(string $module, PrintJob $printJob): string
    {
        return match ($module) {
            'order_details' => route('printing.order_details.preview', $printJob),
            'picking_list' => route('printing.picking_list.preview', $printJob),
            default => route('printing.logistics_labels.preview', $printJob),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $sourcePages
     * @param  Collection<int, PrintJob>|null  $allPrintJobs
     */
    private function resolveOutputTitle(
        PrintJob $printJob,
        int $sheetNumber,
        array $sourcePages,
        ?Collection $allPrintJobs = null,
        bool $isOrderMerge = false,
    ): string {
        $module = (string) ($printJob->module ?? 'logistics_labels');

        if ($isOrderMerge) {
            $mergedName = (string) ($printJob->metadata['original_name'] ?? '');

            return $mergedName !== ''
                ? $mergedName
                : (string) __('merchant.uploads.detail.merge_title');
        }

        if ($module === 'picking_list') {
            $pickingName = (string) ($printJob->metadata['original_name'] ?? '');
            $outputMode = (string) ($printJob->metadata['picking_output_mode'] ?? 'combined');
            $sourceFileName = (string) ($printJob->metadata['source_file_name'] ?? '');

            if ($outputMode === 'separate' && $sourceFileName !== '') {
                return $pickingName !== '' ? $pickingName : $sourceFileName;
            }

            return $pickingName !== ''
                ? $pickingName
                : (string) __('merchant.uploads.detail.picking_title');
        }

        $sourceFileName = (string) ($printJob->metadata['source_file_name'] ?? '');

        if ($sourceFileName === '' && $sourcePages !== []) {
            $sourceFileName = (string) ($sourcePages[0]['original_name'] ?? '');
        }

        $outputMode = (string) ($printJob->metadata['thermal_output_mode'] ?? 'combined');
        $pageCount = (int) ($printJob->metadata['page_count'] ?? 0);
        $totalOutputs = $allPrintJobs?->count() ?? 1;

        if ($outputMode === 'combined' && $totalOutputs === 1) {
            if ($pageCount > 1) {
                return (string) __('merchant.uploads.detail.thermal_combined_title_pages', [
                    'pages' => $pageCount,
                ]);
            }

            return (string) __('merchant.uploads.detail.thermal_combined_title');
        }

        if ($outputMode === 'separate' && $sourceFileName !== '') {
            $totalSheetsForFile = $this->countSheetsForSourceFile($sourceFileName, $allPrintJobs);

            if ($totalSheetsForFile > 1) {
                return (string) __('merchant.uploads.detail.output_file_sheet_title', [
                    'file' => $sourceFileName,
                    'number' => $sheetNumber,
                ]);
            }

            if ($pageCount > 1) {
                return (string) __('merchant.uploads.detail.thermal_file_title_pages', [
                    'file' => $sourceFileName,
                    'pages' => $pageCount,
                ]);
            }

            return $sourceFileName;
        }

        if ($pageCount > 1) {
            return (string) __('merchant.uploads.detail.thermal_output_title_pages', [
                'number' => $sheetNumber,
                'pages' => $pageCount,
            ]);
        }

        return (string) __('merchant.uploads.detail.sheet_title', ['number' => $sheetNumber]);
    }

    /**
     * @param  Collection<int, PrintJob>|null  $allPrintJobs
     */
    private function countSheetsForSourceFile(string $sourceFileName, ?Collection $allPrintJobs = null): int
    {
        if ($allPrintJobs === null || $allPrintJobs->isEmpty()) {
            return 1;
        }

        return $allPrintJobs
            ->filter(static function (PrintJob $job) use ($sourceFileName): bool {
                return (string) ($job->metadata['source_file_name'] ?? '') === $sourceFileName;
            })
            ->count();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildSourceFiles(UploadJob $job): array
    {
        return match ($job->type) {
            UploadJobType::PickingList, UploadJobType::OrderPdf => $this->buildSpreadsheetSourceFiles($job),
            UploadJobType::DeliveryLabel => [
                ...$this->buildPdfSourceFiles($job),
                ...$this->buildSpreadsheetSourceFiles($job),
            ],
            default => $this->buildPdfSourceFiles($job),
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildPdfSourceFiles(UploadJob $job): array
    {
        $fileErrorsByName = collect(
            is_array($job->metadata['file_errors'] ?? null) ? $job->metadata['file_errors'] : [],
        )->keyBy('source_name');

        return $job->pdfUploads
            ->map(function ($pdfUpload) use ($job, $fileErrorsByName): array {
                $exists = Storage::disk($pdfUpload->disk)->exists($pdfUpload->path);
                [$processingStatus, $errorMessage] = $this->resolvePdfSourceFileStatus(
                    $job,
                    (string) $pdfUpload->original_name,
                    $fileErrorsByName->get((string) $pdfUpload->original_name),
                );

                return [
                    'id' => $pdfUpload->id,
                    'list_id' => 'pdf-upload-'.$pdfUpload->id,
                    'name' => $pdfUpload->original_name,
                    'size_kb' => round($pdfUpload->size_bytes / 1024, 1),
                    'file_kind' => 'pdf',
                    'icon' => 'PDF',
                    'processing_status' => $processingStatus,
                    'processing_status_label' => $processingStatus
                        ? (string) __('merchant.uploads.detail.source_file_status.'.$processingStatus)
                        : null,
                    'error_message' => $errorMessage,
                    'preview_url' => $exists
                        ? route('uploads.pdf.preview', [$pdfUpload->upload_job_id, $pdfUpload])
                        : null,
                    'spreadsheet_preview_url' => null,
                    'download_url' => $exists
                        ? route('uploads.pdf.download', [$pdfUpload->upload_job_id, $pdfUpload])
                        : null,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildSpreadsheetSourceFiles(UploadJob $job): array
    {
        $spreadsheetFiles = is_array($job->metadata['spreadsheet_files'] ?? null)
            ? $job->metadata['spreadsheet_files']
            : [];

        $pickingListsByPath = $job->relationLoaded('pickingLists')
            ? $job->pickingLists->keyBy('source_path')
            : collect();
        $spreadsheetProcessing = collect(
            is_array($job->metadata['spreadsheet_processing'] ?? null)
                ? $job->metadata['spreadsheet_processing']
                : [],
        )->keyBy('source_path');

        $files = [];

        foreach ($spreadsheetFiles as $index => $file) {
            if (! is_array($file)) {
                continue;
            }

            $disk = (string) ($file['disk'] ?? 'temp');
            $path = (string) ($file['path'] ?? '');
            $exists = $path !== '' && Storage::disk($disk)->exists($path);
            $pickingList = $pickingListsByPath->get($path);
            $processingEntry = $spreadsheetProcessing->get($path);
            $processingStatus = $this->resolveSpreadsheetSourceFileStatus(
                $job,
                $path,
                $pickingList?->status?->value,
                is_array($processingEntry) ? ($processingEntry['status'] ?? null) : null,
            );
            $errorMessage = (string) ($pickingList?->metadata['error_message'] ?? '')
                ?: (string) (is_array($processingEntry) ? ($processingEntry['error_message'] ?? '') : '')
                ?: $this->resolveSpreadsheetSourceFileError($job, $path);

            $files[] = [
                'id' => 'spreadsheet-'.$index,
                'list_id' => 'spreadsheet-'.$index,
                'name' => (string) ($file['original_name'] ?? basename($path)),
                'size_kb' => round(((int) ($file['size_bytes'] ?? 0)) / 1024, 1),
                'file_kind' => 'spreadsheet',
                'icon' => 'XLS',
                'processing_status' => $processingStatus,
                'processing_status_label' => $processingStatus
                    ? __('merchant.uploads.detail.source_file_status.'.$processingStatus)
                    : null,
                'error_message' => $errorMessage !== '' ? $errorMessage : null,
                'preview_url' => null,
                'spreadsheet_preview_url' => $exists
                    ? route('uploads.spreadsheet.preview', [$job, $index])
                    : null,
                'download_url' => $exists
                    ? route('uploads.spreadsheet.download', [$job, $index])
                    : null,
            ];
        }

        return $files;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildFailedSourceFiles(UploadJob $job): array
    {
        return collect($this->buildSpreadsheetSourceFiles($job))
            ->filter(static fn (array $file): bool => ($file['processing_status'] ?? null) === UploadStatus::Failed->value)
            ->values()
            ->all();
    }

    private function resolveSourceFilesHeading(UploadJob $job): string
    {
        return match ($job->type) {
            UploadJobType::PickingList, UploadJobType::OrderPdf => (string) __('merchant.uploads.detail.uploaded_spreadsheets'),
            UploadJobType::DeliveryLabel => (string) __('merchant.uploads.detail.uploaded_source_files'),
            default => (string) __('merchant.uploads.detail.pdf_files'),
        };
    }

    private function resolveSourceFilesHint(UploadJob $job): ?string
    {
        return match ($job->type) {
            UploadJobType::PickingList, UploadJobType::OrderPdf => (string) __('merchant.uploads.detail.uploaded_spreadsheets_hint'),
            UploadJobType::DeliveryLabel => (string) __('merchant.uploads.detail.uploaded_source_files_hint'),
            default => (string) __('merchant.uploads.detail.pdf_files_hint'),
        };
    }

    private function resolvePrintOutputsHint(UploadJob $job): string
    {
        return match ($job->type) {
            UploadJobType::OrderPdf => (string) __('merchant.uploads.detail.order_pdf_print_outputs_hint'),
            UploadJobType::PickingList => (string) __('merchant.uploads.detail.picking_print_outputs_hint'),
            default => (string) __('merchant.uploads.detail.print_outputs_hint'),
        };
    }

    private function hasSuccessfulOutputs(UploadStatus $status): bool
    {
        return in_array($status, [UploadStatus::Completed, UploadStatus::CompletedWithErrors], true);
    }

    /**
     * @param  list<array<string, mixed>>  $sourcePages
     */
    private function canRegeneratePrintJob(PrintJob $printJob, bool $isThermal, array $sourcePages): bool
    {
        if ($isThermal) {
            return $sourcePages !== [];
        }

        $sourceFiles = is_array($printJob->metadata['source_files'] ?? null)
            ? $printJob->metadata['source_files']
            : [];

        return $sourceFiles !== [];
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function resolvePdfSourceFileStatus(UploadJob $job, string $originalName, mixed $fileError): array
    {
        if (is_array($fileError)) {
            return [
                UploadStatus::Failed->value,
                (string) ($fileError['message'] ?? ''),
            ];
        }

        if ($this->hasSuccessfulOutputs($job->status)) {
            return [UploadStatus::Completed->value, null];
        }

        if ($job->status === UploadStatus::Failed) {
            return [UploadStatus::Failed->value, (string) ($job->error_message ?? '')];
        }

        return [null, null];
    }

    private function resolveSpreadsheetSourceFileStatus(
        UploadJob $job,
        string $path,
        ?string $pickingListStatus,
        ?string $processingStatus,
    ): ?string {
        if ($pickingListStatus !== null && $pickingListStatus !== '') {
            return $pickingListStatus;
        }

        if ($processingStatus !== null && $processingStatus !== '') {
            return $processingStatus;
        }

        if ($this->resolveSpreadsheetSourceFileError($job, $path) !== '') {
            return UploadStatus::Failed->value;
        }

        if (! $this->hasSuccessfulOutputs($job->status)) {
            return null;
        }

        if ($this->sourcePathIncludedInPrintOutputs($job, $path)) {
            return UploadStatus::Completed->value;
        }

        if ($job->status === UploadStatus::Completed) {
            return UploadStatus::Completed->value;
        }

        if ($job->status === UploadStatus::CompletedWithErrors) {
            return UploadStatus::Completed->value;
        }

        return null;
    }

    private function resolveSpreadsheetSourceFileError(UploadJob $job, string $path): string
    {
        $fileErrors = is_array($job->metadata['file_errors'] ?? null) ? $job->metadata['file_errors'] : [];

        foreach ($fileErrors as $error) {
            if (! is_array($error)) {
                continue;
            }

            if (($error['source_path'] ?? '') === $path) {
                return (string) ($error['message'] ?? '');
            }
        }

        return '';
    }

    private function sourcePathIncludedInPrintOutputs(UploadJob $job, string $path): bool
    {
        foreach ($job->printJobs as $printJob) {
            $sourceFiles = is_array($printJob->metadata['source_files'] ?? null)
                ? $printJob->metadata['source_files']
                : [];

            foreach ($sourceFiles as $sourceFile) {
                if (is_array($sourceFile) && ($sourceFile['path'] ?? '') === $path) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $sourcePages
     */
    private function summarizeSourcePages(array $sourcePages): string
    {
        if ($sourcePages === []) {
            return (string) __('merchant.uploads.detail.source_unknown');
        }

        $groups = $this->groupSourcePagesByFile($sourcePages);
        $parts = [];

        foreach ($groups as $group) {
            $pageCount = (int) ($group['page_count'] ?? 0);

            if ($pageCount === 1) {
                $parts[] = (string) __('merchant.uploads.detail.source_file_one_label', [
                    'file' => $group['name'],
                ]);

                continue;
            }

            $parts[] = (string) __('merchant.uploads.detail.source_file_labels', [
                'file' => $group['name'],
                'count' => $pageCount,
            ]);
        }

        return implode(' · ', $parts);
    }

    /**
     * @param  list<array<string, mixed>>  $sourcePages
     * @return list<array{name: string, page_count: int, pages: list<int>}>
     */
    private function groupSourcePagesByFile(array $sourcePages): array
    {
        $grouped = [];

        foreach ($sourcePages as $sourcePage) {
            $name = (string) ($sourcePage['original_name'] ?? __('merchant.uploads.detail.source_file'));
            $page = (int) ($sourcePage['page'] ?? 0);

            if (! array_key_exists($name, $grouped)) {
                $grouped[$name] = [
                    'name' => $name,
                    'pages' => [],
                ];
            }

            if ($page > 0) {
                $grouped[$name]['pages'][] = $page;
            }
        }

        return array_values(array_map(static fn (array $group): array => [
            'name' => $group['name'],
            'page_count' => count($group['pages']),
            'pages' => $group['pages'],
        ], $grouped));
    }

    private function formatThermalLayoutLabel(int $labelCount, int $pageCount, string $layoutMode): string
    {
        if ($pageCount > 1) {
            return (string) __('merchant.uploads.detail.layout_thermal_pdf', [
                'labels' => $labelCount,
                'pages' => $pageCount,
            ]);
        }

        if ($layoutMode === 'a4_single' || $labelCount <= 1) {
            return (string) __('merchant.uploads.detail.layout_single');
        }

        return (string) __('merchant.uploads.detail.layout_multi', ['count' => $labelCount]);
    }

    private function resolvePreviewConfiguration(UploadJob $job, bool $usePdfPreview): PreviewConfiguration
    {
        $configuration = $this->previewConfigurationService->configuration();

        if (! $usePdfPreview) {
            return $configuration;
        }

        $a4 = config('pdf.a4_output', []);

        return new PreviewConfiguration(
            widthMm: (float) ($a4['page_width_mm'] ?? 210.0),
            heightMm: (float) ($a4['page_height_mm'] ?? 297.0),
            aspectRatio: 210.0 / 297.0,
            safeZoneInsetMm: 0.0,
            defaultZoom: $configuration->defaultZoom,
            scalingBehavior: $configuration->scalingBehavior,
        );
    }
}
