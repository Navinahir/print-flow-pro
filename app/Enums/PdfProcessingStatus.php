<?php

declare(strict_types=1);

namespace App\Enums;

enum PdfProcessingStatus: string
{
    case Pending = 'pending';
    case Validated = 'validated';
    case BoundariesDetected = 'boundaries_detected';
    case CanvasPrepared = 'canvas_prepared';
    case NormalizationDeferred = 'normalization_deferred';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('merchant.pdf.status.pending'),
            self::Validated => __('merchant.pdf.status.validated'),
            self::BoundariesDetected => __('merchant.pdf.status.boundaries_detected'),
            self::CanvasPrepared => __('merchant.pdf.status.canvas_prepared'),
            self::NormalizationDeferred => __('merchant.pdf.status.normalization_deferred'),
            self::Completed => __('merchant.pdf.status.completed'),
            self::Failed => __('merchant.pdf.status.failed'),
        };
    }
}
