<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\DTOs\Merchant\Preview\PreviewConfiguration;
use App\DTOs\Merchant\Upload\UploadPreviewResult;
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
        $printJobs = $job->printJobs->sortBy('id')->values();
        $usePdfPreview = $isThermal
            && $job->status === UploadStatus::Completed
            && $printJobs->isNotEmpty();

        return [
            'is_thermal' => $isThermal,
            'use_pdf_preview' => $usePdfPreview,
            'preview_config' => $this->resolvePreviewConfiguration($job, $usePdfPreview),
            'summary' => $this->buildSummary($job),
            'thermal_summary' => $isThermal ? $this->buildThermalSummary($job, $printJobs) : null,
            'print_outputs' => $this->buildPrintOutputs($job, $printJobs),
            'source_files' => $this->buildSourceFiles($job),
            'thermal_output_mode' => \App\Enums\ThermalOutputMode::fromUploadMetadata(
                $job->metadata,
                $job->pdfUploads->count(),
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
        if ($job->status !== UploadStatus::Completed || $printJobs->isEmpty()) {
            return null;
        }

        $totalLabels = 0;

        foreach ($printJobs as $printJob) {
            $totalLabels += (int) ($printJob->metadata['label_count'] ?? 1);
        }

        $hasMultiLayout = $printJobs->contains(
            static fn (PrintJob $printJob): bool => ($printJob->metadata['layout_mode'] ?? '') === 'a4_multi',
        );

        return [
            'source_files' => $job->pdfUploads->count(),
            'total_labels' => $totalLabels,
            'a4_sheets' => $printJobs->count(),
            'layout_key' => $hasMultiLayout ? 'multi' : 'single',
        ];
    }

    /**
     * @param  Collection<int, PrintJob>  $printJobs
     * @return list<array<string, mixed>>
     */
    private function buildPrintOutputs(UploadJob $job, Collection $printJobs): array
    {
        if ($job->type !== UploadJobType::ThermalLabel || $printJobs->isEmpty()) {
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
        $sheetNumber = (int) ($printJob->metadata['sheet_number'] ?? $printJob->source_page_number);
        $labelCount = (int) ($printJob->metadata['label_count'] ?? 1);
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
        $canRegenerate = $canPreview && $uploadStatus === UploadStatus::Completed;

        return [
            'id' => $printJob->id,
            'list_id' => 'print-job-'.$printJob->id,
            'sheet_number' => $sheetNumber,
            'title' => $this->resolveOutputTitle($printJob, $sheetNumber, $sourcePages, $allPrintJobs),
            'layout_mode' => $layoutMode,
            'layout_label' => $layoutMode === 'a4_multi'
                ? (string) __('merchant.uploads.detail.layout_multi', ['count' => $labelCount])
                : (string) __('merchant.uploads.detail.layout_single'),
            'size_label' => (string) __('merchant.uploads.detail.a4_size_label', [
                'width' => (int) $printJob->output_width_mm,
                'height' => (int) $printJob->output_height_mm,
            ]),
            'label_count' => $labelCount,
            'status_label' => $printJob->status->label(),
            'status_value' => $printJob->status->value,
            'source_summary' => $this->summarizeSourcePages($sourcePages),
            'source_pages' => $sourcePages,
            'download_url' => $canDownloadFile
                ? route('printing.logistics_labels.download', $printJob)
                : null,
            'preview_url' => $canPreview
                ? route('printing.logistics_labels.preview', $printJob)
                : null,
            'regenerate_url' => $canRegenerate
                ? route('uploads.print_jobs.regenerate', [$printJob->upload_job_id, $printJob])
                : null,
            'can_regenerate' => $canRegenerate,
        ];
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
    ): string {
        $sourceFileName = (string) ($printJob->metadata['source_file_name'] ?? '');

        if ($sourceFileName === '' && $sourcePages !== []) {
            $sourceFileName = (string) ($sourcePages[0]['original_name'] ?? '');
        }

        $outputMode = (string) ($printJob->metadata['thermal_output_mode'] ?? 'combined');

        if ($outputMode === 'separate' && $sourceFileName !== '') {
            $totalSheetsForFile = $this->countSheetsForSourceFile($sourceFileName, $allPrintJobs);

            if ($totalSheetsForFile > 1) {
                return (string) __('merchant.uploads.detail.output_file_sheet_title', [
                    'file' => $sourceFileName,
                    'number' => $sheetNumber,
                ]);
            }

            return $sourceFileName;
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
        return $job->pdfUploads
            ->map(function ($pdfUpload): array {
                $exists = Storage::disk($pdfUpload->disk)->exists($pdfUpload->path);

                return [
                    'id' => $pdfUpload->id,
                    'list_id' => 'pdf-upload-'.$pdfUpload->id,
                    'name' => $pdfUpload->original_name,
                    'size_kb' => round($pdfUpload->size_bytes / 1024, 1),
                    'preview_url' => $exists
                        ? route('uploads.pdf.preview', [$pdfUpload->upload_job_id, $pdfUpload])
                        : null,
                    'download_url' => $exists
                        ? route('uploads.pdf.download', [$pdfUpload->upload_job_id, $pdfUpload])
                        : null,
                ];
            })
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $sourcePages
     */
    private function summarizeSourcePages(array $sourcePages): string
    {
        if ($sourcePages === []) {
            return (string) __('merchant.uploads.detail.source_unknown');
        }

        $parts = [];

        foreach ($sourcePages as $sourcePage) {
            $name = (string) ($sourcePage['original_name'] ?? __('merchant.uploads.detail.source_file'));
            $page = (int) ($sourcePage['page'] ?? 0);
            $parts[] = (string) __('merchant.uploads.detail.source_page_ref', [
                'file' => $name,
                'page' => $page,
            ]);
        }

        return implode(' · ', $parts);
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
