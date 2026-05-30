<?php

declare(strict_types=1);

namespace App\Enums;

enum PrintingModule: string
{
    case OrderDetails = 'order_details';
    case LogisticsLabels = 'logistics_labels';
    case PickingList = 'picking_list';
    case DeliveryLabels = 'delivery_labels';

    public function featureKey(): string
    {
        return match ($this) {
            self::OrderDetails => 'printing_order_details',
            self::LogisticsLabels => 'printing_logistics_labels',
            self::PickingList => 'printing_picking_list',
            self::DeliveryLabels => 'printing_delivery_labels',
        };
    }

    public function routeName(): string
    {
        return 'printing.'.$this->value.'.index';
    }

    public function routeUrl(): string
    {
        return route($this->routeName());
    }

    public function navLabelKey(): string
    {
        return match ($this) {
            self::OrderDetails => 'merchant.nav.order_details',
            self::LogisticsLabels => 'merchant.nav.logistics_labels',
            self::PickingList => 'merchant.nav.picking_list',
            self::DeliveryLabels => 'merchant.nav.delivery_labels',
        };
    }

    public function viewName(): string
    {
        return match ($this) {
            self::OrderDetails => 'merchant.printing.order-details.index',
            self::LogisticsLabels => 'merchant.printing.logistics-labels.index',
            self::PickingList => 'merchant.printing.picking-list.index',
            self::DeliveryLabels => 'merchant.printing.delivery-labels.index',
        };
    }

    public function translationKey(): string
    {
        return 'merchant.printing.modules.'.$this->value;
    }

    /**
     * @return list<self>
     */
    public static function navigable(): array
    {
        return self::cases();
    }

    public static function fromRouteKey(string $key): self
    {
        return self::from(str_replace('-', '_', $key));
    }
}
