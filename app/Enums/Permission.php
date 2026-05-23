<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case AccessAdminPanel = 'access_admin_panel';

    case ViewUsers = 'users.view';
    case ManageUsers = 'users.manage';

    case ViewRoles = 'roles.view';
    case ManageRoles = 'roles.manage';

    case ViewMerchants = 'merchants.view';
    case ManageMerchants = 'merchants.manage';

    case ViewBilling = 'billing.view';
    case ManageBilling = 'billing.manage';

    case ViewAuditLogs = 'audit_logs.view';

    case ViewSupportTickets = 'support_tickets.view';
    case ManageSupportTickets = 'support_tickets.manage';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Flat list of permission names defined in config/permissions.php.
     *
     * @return list<string>
     */
    public static function fromConfig(): array
    {
        $permissions = [];

        foreach (config('permissions.groups', []) as $groupPermissions) {
            $permissions = array_merge($permissions, $groupPermissions);
        }

        return array_values(array_unique($permissions));
    }
}
