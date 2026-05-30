<?php

declare(strict_types=1);

namespace App\Support;

use App\Services\Domain\DomainConfigurationService;

final class MerchantConfig
{
    /**
     * @return mixed
     */
    public static function get(?string $key = null, mixed $default = null): mixed
    {
        /** @var DomainConfigurationService $service */
        $service = app(DomainConfigurationService::class);

        if ($key === null) {
            return $service->current();
        }

        return $service->get($key, $default);
    }

    public static function feature(string $featureKey, bool $default = false): bool
    {
        /** @var DomainConfigurationService $service */
        $service = app(DomainConfigurationService::class);

        if ($service->current() !== null) {
            return $service->isFeatureEnabled($featureKey);
        }

        $regionKey = config('app.region_key');

        if (! is_string($regionKey) || $regionKey === '') {
            $regionKey = $service->primaryActiveRegionKey();
        }

        return $service->isFeatureEnabled($featureKey, $regionKey);
    }
}
