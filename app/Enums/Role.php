<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case RegionalPartner = 'regional_partner';
    case Merchant = 'merchant';

    /**
     * Roles that may access the Filament admin panel (when granted access_admin_panel).
     *
     * @return list<self>
     */
    public static function adminRoles(): array
    {
        return [
            self::SuperAdmin,
            self::RegionalPartner,
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isAdmin(): bool
    {
        return in_array($this, self::adminRoles(), true);
    }
}
