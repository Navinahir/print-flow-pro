<?php

declare(strict_types=1);

return [

    'default_locale' => 'zh-TW',

    'locale_storage_key' => 'xycubic-marketing-locale',

    'locale_cookie' => 'xycubic-marketing-locale',

    'theme_storage_key' => 'xycubic-marketing-theme',

    'default_theme' => 'dark',

    /*
    |--------------------------------------------------------------------------
    | Marketing locales
    |--------------------------------------------------------------------------
    |
    | Keys match Laravel locale names (lang/{locale}/marketing.php).
    | Labels are translated via marketing.locales.{key}.
    |
    */

    'locales' => [
        'zh-TW' => [
            'route' => 'marketing.tw',
        ],
        'en' => [
            'route' => 'marketing.en',
        ],
    ],

];
