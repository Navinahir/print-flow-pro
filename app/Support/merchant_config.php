<?php

declare(strict_types=1);

namespace App\Support;

use App\DTOs\Domain\MerchantDomainConfig;
use App\Services\Domain\DomainConfigurationService;

if (! function_exists('merchant_config')) {
    /**
     * @return mixed|MerchantDomainConfig|null
     */
    function merchant_config(?string $key = null, mixed $default = null): mixed
    {
        return MerchantConfig::get($key, $default);
    }
}

if (! function_exists('merchant_feature')) {
    function merchant_feature(string $featureKey, bool $default = false): bool
    {
        return MerchantConfig::feature($featureKey, $default);
    }
}
