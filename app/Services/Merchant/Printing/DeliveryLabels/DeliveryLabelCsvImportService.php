<?php

declare(strict_types=1);

namespace App\Services\Merchant\Printing\DeliveryLabels;

use App\DTOs\Merchant\Printing\DeliveryLabels\DeliveryLabelCsvImportResult;
use App\DTOs\Merchant\Printing\DeliveryLabels\DeliveryLabelListItemData;
use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\DeliveryLabel;
use App\Models\Merchant;
use App\Models\UploadJob;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryLabelCsvImportService
{
    public function __construct(
        private readonly CourierCsvReaderService $csvReader,
        private readonly CourierCsvHeaderDetector $headerDetector,
        private readonly DeliveryLabelCsvRowMapper $rowMapper,
        private readonly DeliveryLabelPreviewService $previewService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function import(User $user, Merchant $merchant, UploadedFile $file): DeliveryLabelCsvImportResult
    {
        $parsed = $this->csvReader->read($file);
        $headers = $parsed['headers'];
        $rows = $parsed['rows'];

        if ($headers === []) {
            throw ValidationException::withMessages([
                'file' => [__('merchant.delivery_labels.csv.validation.headers_missing')],
            ]);
        }

        $columns = $this->headerDetector->detectColumns($headers);

        if ($columns['address'] === null && $columns['recipient'] === null) {
            throw ValidationException::withMessages([
                'file' => [__('merchant.delivery_labels.csv.validation.columns_missing')],
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => [__('merchant.delivery_labels.csv.validation.rows_missing')],
            ]);
        }

        return DB::transaction(function () use ($user, $merchant, $file, $headers, $rows, $columns): DeliveryLabelCsvImportResult {
            $job = UploadJob::query()->create([
                'merchant_id' => $merchant->id,
                'user_id' => $user->id,
                'uploaded_by' => $user->id,
                'type' => UploadJobType::DeliveryLabel,
                'status' => UploadStatus::Completed,
                'file_count' => 1,
                'metadata' => [
                    'original_names' => [$file->getClientOriginalName()],
                    'csv_headers' => $headers,
                    'detected_columns' => $columns,
                ],
            ]);

            $storedPath = $file->storeAs(
                "merchants/{$merchant->id}/jobs/{$job->id}",
                $file->getClientOriginalName(),
                'temp',
            );

            if ($storedPath !== false) {
                $metadata = $job->metadata ?? [];
                $metadata['csv_path'] = $storedPath;
                $job->update(['metadata' => $metadata]);
            }

            $listItems = [];

            foreach ($rows as $index => $row) {
                $mapped = $this->rowMapper->map($headers, $row, $columns);

                if ($mapped['courier_address'] === null && $mapped['recipient_name'] === null) {
                    continue;
                }

                $courierAddress = $mapped['courier_address']
                    ?? (string) __('merchant.delivery_labels.csv.fallback_address');

                $label = DeliveryLabel::query()->create([
                    'merchant_id' => $merchant->id,
                    'upload_job_id' => $job->id,
                    'recipient_name' => $mapped['recipient_name'],
                    'address_line_1' => $mapped['address_line_1'],
                    'status' => UploadStatus::Completed,
                    'metadata' => $mapped['metadata'],
                ]);

                $listItems[] = $this->buildListItemFromModel($label, $index + 1);
            }

            if ($listItems === []) {
                throw ValidationException::withMessages([
                    'file' => [__('merchant.delivery_labels.csv.validation.no_valid_rows')],
                ]);
            }

            $this->auditLogService->logUpload(
                event: 'delivery_labels.csv.imported',
                description: "Imported {$job->id} delivery label CSV with ".count($listItems).' rows.',
                auditable: $job,
                merchant: $merchant,
                properties: ['imported_count' => count($listItems)],
            );

            return new DeliveryLabelCsvImportResult(
                uploadJobId: $job->id,
                importedCount: count($listItems),
                items: array_map(
                    static fn (DeliveryLabelListItemData $item): array => $item->toArray(),
                    $listItems,
                ),
                detectedColumns: $columns,
            );
        });
    }

    public function buildListItemFromModel(DeliveryLabel $label, int $position): DeliveryLabelListItemData
    {
        $metadata = $label->metadata ?? [];
        $courierAddress = $label->address_line_1
            ?? (string) ($metadata['courier_address'] ?? '');

        $recipient = $label->recipient_name
            ?? (string) __('merchant.delivery_labels.csv.unknown_recipient');

        return new DeliveryLabelListItemData(
            id: 'delivery-label-'.$label->id,
            title: $recipient,
            subtitle: (string) __('merchant.delivery_labels.csv.list_subtitle', ['id' => $label->id]),
            status: 'ready',
            width: 1500,
            height: 1000,
            preview: $this->previewService->buildPreview(
                recipientName: $recipient,
                courierAddress: $courierAddress,
                remarks: $metadata['remarks'] ?? null,
                trackingNumber: $metadata['tracking_number'] ?? null,
                carrier: $metadata['carrier'] ?? null,
            ),
        );
    }
}
