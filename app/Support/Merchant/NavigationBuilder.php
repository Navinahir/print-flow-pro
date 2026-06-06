<?php

declare(strict_types=1);

namespace App\Support\Merchant;

use App\Enums\PrintingModule;
use App\Support\MerchantConfig;

final class NavigationBuilder
{
    /**
     * @return list<array{label: string, route: string, route_name: string, active: bool, icon: string}>
     */
    public static function primaryLinks(?string $currentRouteName = null): array
    {
        $links = [
            [
                'label' => __('merchant.nav.dashboard'),
                'route' => route('dashboard'),
                'route_name' => 'dashboard',
                'active' => $currentRouteName === 'dashboard',
                'icon' => 'dashboard',
            ],
        ];

        if (MerchantConfig::feature('uploads')) {
            $links[] = [
                'label' => __('merchant.nav.uploads'),
                'route' => route('uploads.index'),
                'route_name' => 'uploads.index',
                'active' => str_starts_with((string) $currentRouteName, 'uploads.'),
                'icon' => 'uploads',
            ];
        }

        return $links;
    }

    /**
     * @return list<array{label: string, route: string, route_name: string, active: bool, module: PrintingModule}>
     */
    public static function printingLinks(?string $currentRouteName = null): array
    {
        return PrintingNavigation::items($currentRouteName);
    }

    /**
     * @return list<array{label: string, route: string, route_name: string, active: bool}>
     */
    public static function mobileLinks(?string $currentRouteName = null): array
    {
        $links = self::primaryLinks($currentRouteName);

        foreach (self::printingLinks($currentRouteName) as $item) {
            $links[] = [
                'label' => $item['label'],
                'route' => route($item['module']->routeName()),
                'route_name' => $item['route_name'],
                'active' => $item['active'],
            ];
        }

        if (MerchantConfig::feature('uploads')) {
            $links[] = [
                'label' => __('merchant.nav.new_upload'),
                'route' => route('uploads.create'),
                'route_name' => 'uploads.create',
                'active' => $currentRouteName === 'uploads.create',
            ];
        }

        return $links;
    }
}
