<?php

declare(strict_types=1);

/**
 * Multi-domain SaaS routing configuration.
 *
 * Infrastructure hosts (marketing, admin) remain env-driven.
 * Merchant domain settings (hosts, locales, branding, features) are stored in the
 * database and loaded via DomainConfigurationService.
 */
$environment = env('APP_ENV', 'production');

$localDefaults = [
    'marketing' => 'localhost:8000',
    'admin' => 'localhost:8002',
];

$stagingDefaults = [
    'marketing' => 'xycubic.com',
    'admin' => 'manage-xy.xycubic.com',
];

$productionDefaults = $stagingDefaults;

$defaults = match ($environment) {
    'local' => $localDefaults,
    'staging' => $stagingDefaults,
    default => $productionDefaults,
};

$fallbackMerchantRegions = [
    'tw' => [
        'domain' => $environment === 'local' ? 'localhost:8001' : 'tw.xycubic.com',
        'country_code' => 'TW',
        'locale' => 'zh-TW',
        'locale_label' => 'Traditional Chinese',
        'active' => true,
        'sort_order' => 1,
        'session_cookie' => 'xycubic-merchant-tw-session',
        'brand_name' => 'XY Cubic Shopee',
        'brand_tagline' => 'Print-ready workflows for Shopee sellers',
        'locales' => [
            ['locale' => 'zh-TW', 'label' => 'Traditional Chinese', 'is_default' => true],
            ['locale' => 'en', 'label' => 'English', 'is_default' => false],
        ],
        'features' => [
            'uploads' => true,
            'printing_order_details' => true,
            'printing_logistics_labels' => true,
            'printing_picking_list' => true,
            'printing_delivery_labels' => true,
        ],
    ],
    'ph' => [
        'domain' => $environment === 'local' ? 'localhost:8003' : 'ph.xycubic.com',
        'country_code' => 'PH',
        'locale' => 'en-PH',
        'locale_label' => 'English (Philippines)',
        'active' => false,
        'sort_order' => 2,
        'session_cookie' => 'xycubic-merchant-ph-session',
        'brand_name' => 'XY Cubic Shopee',
        'brand_tagline' => 'Print-ready workflows for Shopee sellers',
        'locales' => [
            ['locale' => 'en-PH', 'label' => 'English (Philippines)', 'is_default' => true],
        ],
        'features' => [
            'uploads' => true,
        ],
    ],
    'vn' => [
        'domain' => $environment === 'local' ? 'localhost:8004' : 'vn.xycubic.com',
        'country_code' => 'VN',
        'locale' => 'vi-VN',
        'locale_label' => 'Tiếng Việt',
        'active' => false,
        'sort_order' => 3,
        'session_cookie' => 'xycubic-merchant-vn-session',
        'brand_name' => 'XY Cubic Shopee',
        'brand_tagline' => 'Print-ready workflows for Shopee sellers',
        'locales' => [
            ['locale' => 'vi-VN', 'label' => 'Tiếng Việt', 'is_default' => true],
        ],
        'features' => [
            'uploads' => true,
        ],
    ],
];

return [

    /*
    |--------------------------------------------------------------------------
    | Domain routing toggle (infrastructure)
    |--------------------------------------------------------------------------
    */

    'routing_enabled' => filter_var(env('DOMAIN_ROUTING_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    | When true, marketing/admin routes skip Route::domain() so artisan serve :8000 works.
    | Must stay false in production — otherwise /tw is exposed on every host.
    */
    'port_routing' => filter_var(env('DOMAIN_PORT_ROUTING', false), FILTER_VALIDATE_BOOLEAN),

    'environment' => $environment,

    /*
    |--------------------------------------------------------------------------
    | Marketing (public site — APP_ENV defaults; runtime host from DB)
    |--------------------------------------------------------------------------
    */

    'marketing' => [
        'domain' => $defaults['marketing'],
        'locale_prefixes' => [
            'tw' => 'zh-TW',
        ],
        'session_cookie' => env('SESSION_COOKIE_MARKETING', 'xycubic-marketing-session'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Management dashboard (Filament — APP_ENV defaults; path_prefix also in DB)
    |--------------------------------------------------------------------------
    */

    'admin' => [
        'domain' => $defaults['admin'],
        'path_prefix' => 'boss',
        'session_cookie' => env('SESSION_COOKIE_ADMIN', 'xycubic-admin-session'),
        'blocked_paths' => ['admin', 'login', 'register', 'dashboard', 'uploads'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Merchant fallback (used before DB seed / when database unavailable)
    |--------------------------------------------------------------------------
    |
    | Runtime merchant configuration is loaded from domain_settings and related
    | tables. These values bootstrap DomainSettingSeeder and provide a safe
    | fallback for install, migrate, and test environments.
    |
    */

    'fallback_merchants' => $fallbackMerchantRegions,

    /*
    |--------------------------------------------------------------------------
    | Marketing / admin fallback (seeded into domain_settings; env optional)
    |--------------------------------------------------------------------------
    */

    'fallback_infrastructure' => [
        'marketing' => [
            'host' => $defaults['marketing'],
            'country_code' => '--',
            'session_cookie' => 'xycubic-marketing-session',
        ],
        'admin' => [
            'host' => $defaults['admin'],
            'country_code' => '--',
            'session_cookie' => 'xycubic-admin-session',
            'path_prefix' => 'boss',
        ],
    ],

    'fallback_brand' => [
        'name' => 'XY Cubic Shopee',
        'tagline' => 'Print-ready workflows for Shopee sellers',
        'logo' => null,
        'favicon' => null,
    ],

    'fallback_settings' => [
        'upload' => [
            'max_file_size_kb' => 20480,
            'max_files_per_job' => 20,
        ],
        'preview' => [
            'width_mm' => 150,
            'height_mm' => 100,
            'aspect_ratio' => 1.5,
            'safe_zone_inset_mm' => 5,
            'default_zoom' => 1.0,
            'scaling_behavior' => 'fit',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Local port reference (documentation / artisan serve helpers)
    |--------------------------------------------------------------------------
    */

    'local_ports' => [
        'marketing' => (int) env('LOCAL_PORT_MARKETING', 8000),
        'merchant_tw' => (int) env('LOCAL_PORT_MERCHANT_TW', 8001),
        'admin' => (int) env('LOCAL_PORT_ADMIN', 8002),
        'merchant_ph' => (int) env('LOCAL_PORT_MERCHANT_PH', 8003),
        'merchant_vn' => (int) env('LOCAL_PORT_MERCHANT_VN', 8004),
    ],

    /*
    |--------------------------------------------------------------------------
    | Runtime context (populated by ResolveRegion middleware)
    |--------------------------------------------------------------------------
    */

    'current' => [
        'surface' => null,
        'region_key' => null,
        'country_code' => null,
        'locale' => null,
        'domain' => null,
        'host' => null,
        'active' => null,
    ],

];
