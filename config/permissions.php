<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authorization guard
    |--------------------------------------------------------------------------
    |
    | Must match the guard used by Filament and Breeze (web).
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Permission groups
    |--------------------------------------------------------------------------
    |
    | Dot-notation permission names grouped by domain. Add new permissions here
    | and mirror them in App\Enums\Permission for type-safe checks in code.
    |
    */

    'groups' => [
        'panel' => [
            'access_admin_panel',
        ],
        'users' => [
            'users.view',
            'users.manage',
        ],
        'roles' => [
            'roles.view',
            'roles.manage',
        ],
        'merchants' => [
            'merchants.view',
            'merchants.manage',
        ],
        'billing' => [
            'billing.view',
            'billing.manage',
        ],
        'audit' => [
            'audit_logs.view',
        ],
        'support' => [
            'support_tickets.view',
            'support_tickets.manage',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role → permission assignments
    |--------------------------------------------------------------------------
    |
    | Two roles only: admin and merchant.
    | Surface access (which host) is enforced via users.role + middleware.
    | Keep this lightweight for milestone scope.
    |
    */

    'role_permissions' => [
        'admin' => [
            'access_admin_panel',
            'merchants.view',
            'merchants.manage',
            'audit_logs.view',
            'support_tickets.view',
            'support_tickets.manage',
        ],
        'merchant' => [],
    ],

];
