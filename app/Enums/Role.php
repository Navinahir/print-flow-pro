<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Merchant = 'merchant';

    /**
     * @return list<self>
     */
    public static function adminSurfaceRoles(): array
    {
        return [
            self::Admin,
        ];
    }

    /**
     * @return list<self>
     */
    public static function merchantSurfaceRoles(): array
    {
        return [
            self::Merchant,
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canAccessAdminSurface(): bool
    {
        return in_array($this, self::adminSurfaceRoles(), true);
    }

    public function canAccessMerchantSurface(): bool
    {
        return in_array($this, self::merchantSurfaceRoles(), true);
    }
}
