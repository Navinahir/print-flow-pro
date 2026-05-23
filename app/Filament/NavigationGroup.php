<?php

declare(strict_types=1);

namespace App\Filament;

enum NavigationGroup: string
{
    case Overview = 'Overview';
    case MerchantsBilling = 'Merchants & Billing';
    case Operations = 'Operations';
    case System = 'System';
}
