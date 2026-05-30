<?php

declare(strict_types=1);

namespace App\Enums;

enum PrintingPreviewType: string
{
    case OrderDetails = 'order_details';
    case LogisticsLabels = 'logistics_labels';
    case PickingList = 'picking_list';
    case DeliveryLabels = 'delivery_labels';
}
