<?php

declare(strict_types=1);

namespace App\Enums;

enum UploadJobType: string
{
    case OrderPdf = 'order_pdf';
    case ThermalLabel = 'thermal_label';
    case PickingList = 'picking_list';
    case DeliveryLabel = 'delivery_label';

    public function label(): string
    {
        return match ($this) {
            self::OrderPdf => __('merchant.uploads.types.order_pdf'),
            self::ThermalLabel => __('merchant.uploads.types.thermal_label'),
            self::PickingList => __('merchant.uploads.types.picking_list'),
            self::DeliveryLabel => __('merchant.uploads.types.delivery_label'),
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<string>
     */
    public static function allowedMimeTypes(): array
    {
        return match ($this) {
            self::OrderPdf, self::ThermalLabel, self::DeliveryLabel => [
                'application/pdf',
                'text/csv',
                'text/plain',
            ],
            self::PickingList => [
                'text/csv',
                'text/plain',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        };
    }

    /**
     * @return list<string>
     */
    public function fileExtensions(): array
    {
        return match ($this) {
            self::OrderPdf, self::ThermalLabel, self::DeliveryLabel => ['pdf', 'csv'],
            self::PickingList => ['csv', 'xlsx', 'xls'],
        };
    }
}
