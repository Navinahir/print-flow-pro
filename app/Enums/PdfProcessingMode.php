<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Identifies which PDF pipeline profile to run.
 * Maps to UploadJobType in module processors (implemented in later M2 phases).
 */
enum PdfProcessingMode: string
{
    case ThermalLabel = 'thermal_label';
    case OrderPdfMerge = 'order_pdf_merge';
    case DeliveryLabel = 'delivery_label';
    case PickingListExport = 'picking_list_export';

    public function label(): string
    {
        return match ($this) {
            self::ThermalLabel => __('merchant.pdf.modes.thermal_label'),
            self::OrderPdfMerge => __('merchant.pdf.modes.order_pdf_merge'),
            self::DeliveryLabel => __('merchant.pdf.modes.delivery_label'),
            self::PickingListExport => __('merchant.pdf.modes.picking_list_export'),
        };
    }

    public static function fromUploadJobType(UploadJobType $type): self
    {
        return match ($type) {
            UploadJobType::OrderPdf => self::OrderPdfMerge,
            UploadJobType::ThermalLabel => self::ThermalLabel,
            UploadJobType::DeliveryLabel => self::DeliveryLabel,
            UploadJobType::PickingList => self::PickingListExport,
        };
    }

    public function configKey(): string
    {
        return $this->value;
    }
}
