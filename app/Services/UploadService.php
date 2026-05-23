<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\Merchant;
use App\Models\PdfUpload;
use App\Models\UploadJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UploadService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  list<UploadedFile>  $files
     */
    public function createJob(
        User $user,
        Merchant $merchant,
        UploadJobType $type,
        array $files,
    ): UploadJob {
        return DB::transaction(function () use ($user, $merchant, $type, $files): UploadJob {
            $job = UploadJob::query()->create([
                'merchant_id' => $merchant->id,
                'user_id' => $user->id,
                'uploaded_by' => $user->id,
                'type' => $type,
                'status' => UploadStatus::Pending,
                'file_count' => count($files),
                'metadata' => [
                    'original_names' => array_map(
                        static fn (UploadedFile $file): string => $file->getClientOriginalName(),
                        $files,
                    ),
                ],
            ]);

            foreach ($files as $file) {
                $this->storeFile($job, $merchant, $file, $type);
            }

            $this->auditLogService->logUpload(
                event: 'upload.received',
                description: "Upload job #{$job->id} received ({$type->value}).",
                auditable: $job,
                merchant: $merchant,
                properties: ['file_count' => count($files)],
            );

            return $job->fresh(['pdfUploads', 'uploadedBy']);
        });
    }

    private function storeFile(
        UploadJob $job,
        Merchant $merchant,
        UploadedFile $file,
        UploadJobType $type,
    ): ?PdfUpload {
        $extension = strtolower($file->getClientOriginalExtension());
        $storedName = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs(
            "merchants/{$merchant->id}/jobs/{$job->id}",
            $storedName,
            'temp',
        );

        if ($path === false) {
            return null;
        }

        if (in_array($extension, ['pdf'], true)) {
            return PdfUpload::query()->create([
                'merchant_id' => $merchant->id,
                'upload_job_id' => $job->id,
                'original_name' => $file->getClientOriginalName(),
                'disk' => 'temp',
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize() ?: 0,
                'status' => UploadStatus::Pending,
                'checksum' => hash_file('sha256', $file->getRealPath()) ?: null,
            ]);
        }

        // Spreadsheet picking lists — stored on job metadata until PickingList processor runs.
        $metadata = $job->metadata ?? [];
        $files = $metadata['spreadsheet_files'] ?? [];
        $files[] = [
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'temp',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize() ?: 0,
        ];
        $metadata['spreadsheet_files'] = $files;
        $job->update(['metadata' => $metadata]);

        return null;
    }
}
