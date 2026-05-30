<?php

declare(strict_types=1);

namespace App\Support\Merchant;

use App\Enums\PrintingModule;
use App\Support\MerchantConfig;

final class PrintingNavigation
{
    /**
     * @return list<array{
     *     module: PrintingModule,
     *     label: string,
     *     route: string,
     *     route_name: string,
     *     enabled: bool,
     *     active: bool
     * }>
     */
    public static function items(?string $currentRouteName = null): array
    {
        $items = [];

        foreach (PrintingModule::navigable() as $module) {
            $enabled = MerchantConfig::feature($module->featureKey());

            if (! $enabled) {
                continue;
            }

            $items[] = [
                'module' => $module,
                'label' => __($module->navLabelKey()),
                'route' => $module->routeName(),
                'route_name' => $module->routeName(),
                'enabled' => true,
                'active' => $currentRouteName === $module->routeName(),
            ];
        }

        return $items;
    }

    public static function hasAnyEnabled(): bool
    {
        foreach (PrintingModule::navigable() as $module) {
            if (MerchantConfig::feature($module->featureKey())) {
                return true;
            }
        }

        return false;
    }
}
