<?php

declare(strict_types=1);

namespace App\Actions\Merchant\Upload;

use App\Models\DeliveryLabel;
use App\Models\PdfUpload;
use App\Models\PickingList;
use App\Models\PrintJob;
use App\Models\UploadJob;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteUploadJob
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function execute(UploadJob $uploadJob): void
    {
        DB::transaction(function () use ($uploadJob): void {
            $uploadJob->loadMissing(['printJobs', 'pdfUploads', 'merchant', 'deliveryLabels', 'pickingLists']);

            foreach ($uploadJob->printJobs as $printJob) {
                $this->deleteStoredFile($printJob->output_disk, $printJob->output_path);
            }

            foreach ($uploadJob->pdfUploads as $pdfUpload) {
                $this->deleteStoredFile($pdfUpload->disk, $pdfUpload->path);
            }

            foreach ($uploadJob->deliveryLabels as $deliveryLabel) {
                $this->deleteStoredFile($deliveryLabel->output_disk, $deliveryLabel->output_path);
            }

            foreach ($uploadJob->pickingLists as $pickingList) {
                $this->deleteStoredFile($pickingList->source_disk, $pickingList->source_path);
                $this->deleteStoredFile($pickingList->output_disk, $pickingList->output_path);
            }

            $spreadsheetFiles = is_array($uploadJob->metadata['spreadsheet_files'] ?? null)
                ? $uploadJob->metadata['spreadsheet_files']
                : [];

            foreach ($spreadsheetFiles as $file) {
                if (is_array($file)) {
                    $this->deleteStoredFile($file['disk'] ?? null, $file['path'] ?? null);
                }
            }

            $merchant = $uploadJob->merchant;

            if ($merchant !== null) {
                $this->auditLogService->logUpload(
                    event: 'upload.deleted',
                    description: "Upload job #{$uploadJob->id} deleted.",
                    auditable: $uploadJob,
                    merchant: $merchant,
                );
            }

            PrintJob::query()->where('upload_job_id', $uploadJob->id)->delete();
            DeliveryLabel::query()->where('upload_job_id', $uploadJob->id)->delete();
            PickingList::query()->where('upload_job_id', $uploadJob->id)->delete();
            PdfUpload::query()->where('upload_job_id', $uploadJob->id)->delete();

            DB::table('audit_logs')
                ->where('auditable_type', UploadJob::class)
                ->where('auditable_id', $uploadJob->id)
                ->delete();

            $uploadJob->delete();
        });
    }

    private function deleteStoredFile(?string $disk, ?string $path): void
    {
        if ($disk === null || $path === null || $path === '') {
            return;
        }

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
