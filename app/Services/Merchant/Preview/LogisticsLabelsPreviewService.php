<?php

declare(strict_types=1);

namespace App\Services\Merchant\Preview;

use App\DTOs\Merchant\Preview\LogisticsLabelsPreviewData;
use App\Enums\PrintJobStatus;
use App\Models\PrintJob;

class LogisticsLabelsPreviewService
{
    public function buildSamplePreview(string $suffix = '1'): LogisticsLabelsPreviewData
    {
        return new LogisticsLabelsPreviewData(
            trackingNumber: (string) __('merchant.printing.preview.logistics_labels.samples.tracking_number', ['id' => $suffix]),
            carrier: (string) __('merchant.printing.preview.logistics_labels.samples.carrier'),
            recipientName: (string) __('merchant.printing.preview.logistics_labels.samples.recipient_name'),
            recipientAddress: (string) __('merchant.printing.preview.logistics_labels.samples.recipient_address'),
            shipmentDate: (string) __('merchant.printing.preview.logistics_labels.samples.shipment_date'),
            serviceLevel: (string) __('merchant.printing.preview.logistics_labels.samples.service_level'),
        );
    }

    public function buildFromPrintJob(PrintJob $printJob): LogisticsLabelsPreviewData
    {
        $metrics = is_array($printJob->metadata['metrics'] ?? null)
            ? $printJob->metadata['metrics']
            : [];

        $orientation = (string) ($metrics['orientation'] ?? $printJob->source_orientation ?? '');
        $width = $printJob->source_width_mm ?? ($metrics['width_mm'] ?? null);
        $height = $printJob->source_height_mm ?? ($metrics['height_mm'] ?? null);

        $downloadUrl = $printJob->status === PrintJobStatus::Ready
            ? route('printing.logistics_labels.download', $printJob)
            : null;

        return new LogisticsLabelsPreviewData(
            trackingNumber: (string) __('merchant.printing.preview.logistics_labels.processed.tracking_number', [
                'job' => $printJob->id,
                'page' => $printJob->source_page_number,
            ]),
            carrier: (string) __('merchant.printing.preview.logistics_labels.processed.carrier'),
            recipientName: (string) __('merchant.printing.preview.logistics_labels.processed.recipient_name'),
            recipientAddress: (string) __('merchant.printing.preview.logistics_labels.processed.recipient_address', [
                'width' => $width,
                'height' => $height,
                'orientation' => $orientation !== ''
                    ? __("merchant.pdf.orientation.{$orientation}")
                    : __('merchant.printing.preview.logistics_labels.processed.unknown_orientation'),
            ]),
            shipmentDate: $printJob->created_at?->format('Y-m-d H:i') ?? '',
            serviceLevel: (string) __('merchant.printing.preview.logistics_labels.processed.service_level', [
                'width' => $printJob->output_width_mm,
                'height' => $printJob->output_height_mm,
            ]),
            downloadUrl: $downloadUrl,
            sourceWidthMm: $width !== null ? (float) $width : null,
            sourceHeightMm: $height !== null ? (float) $height : null,
            outputWidthMm: $printJob->output_width_mm,
            outputHeightMm: $printJob->output_height_mm,
            pageNumber: $printJob->source_page_number,
            printJobStatus: $printJob->status->value,
        );
    }
}
