<?php

declare(strict_types=1);

namespace App\Enums;

enum AuditLogChannel: string
{
    case Admin = 'admin';
    case Upload = 'upload';
    case Auth = 'auth';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Upload => 'Upload',
            self::Auth => 'Authentication',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
