<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Scopes\CountryCodeScope;
use App\Support\CountryScope;

trait BelongsToCountry
{
    public static function bootBelongsToCountry(): void
    {
        static::addGlobalScope(new CountryCodeScope);

        static::creating(function ($model): void {
            if (! empty($model->country_code)) {
                return;
            }

            $countryCode = CountryScope::currentCountryCode();

            if ($countryCode !== null) {
                $model->country_code = $countryCode;
            }
        });
    }
}
