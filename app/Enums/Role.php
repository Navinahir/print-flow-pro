<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case RegionalPartner = 'regional_partner';
    case Merchant = 'merchant';
}
