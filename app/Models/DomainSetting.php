<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $region_key
 * @property string $host
 * @property string $country_code
 * @property string $surface
 * @property bool $is_active
 * @property string|null $session_cookie
 * @property string $brand_name
 * @property string|null $brand_tagline
 * @property string|null $brand_logo
 * @property string|null $brand_favicon
 * @property array<string, mixed>|null $settings
 * @property int $sort_order
 * @property-read Collection<int, DomainLocale> $locales
 * @property-read Collection<int, DomainFeature> $features
 */
class DomainSetting extends Model
{
    public const SURFACE_MARKETING = 'marketing';

    public const SURFACE_MERCHANT = 'merchant';

    public const SURFACE_ADMIN = 'admin';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'region_key',
        'host',
        'country_code',
        'surface',
        'is_active',
        'session_cookie',
        'brand_name',
        'brand_tagline',
        'brand_logo',
        'brand_favicon',
        'settings',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<DomainLocale, $this>
     */
    public function locales(): HasMany
    {
        return $this->hasMany(DomainLocale::class);
    }

    /**
     * @return HasMany<DomainFeature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(DomainFeature::class);
    }

    public function defaultLocale(): ?DomainLocale
    {
        return $this->locales->firstWhere('is_default', true)
            ?? $this->locales->first();
    }
}
