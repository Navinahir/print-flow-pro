<?php

declare(strict_types=1);

namespace App\Support\Domains;

final readonly class DomainContext
{
    public const SURFACE_MARKETING = 'marketing';

    public const SURFACE_MERCHANT = 'merchant';

    public const SURFACE_ADMIN = 'admin';

    public const SURFACE_UNKNOWN = 'unknown';

    public function __construct(
        public string $surface,
        public ?string $regionKey,
        public ?string $countryCode,
        public string $locale,
        public string $host,
        public ?string $domain,
        public bool $active,
    ) {}

    public function isMarketing(): bool
    {
        return $this->surface === self::SURFACE_MARKETING;
    }

    public function isMerchant(): bool
    {
        return $this->surface === self::SURFACE_MERCHANT;
    }

    public function isAdmin(): bool
    {
        return $this->surface === self::SURFACE_ADMIN;
    }

    public function isKnown(): bool
    {
        return $this->surface !== self::SURFACE_UNKNOWN;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'surface' => $this->surface,
            'region_key' => $this->regionKey,
            'country_code' => $this->countryCode,
            'locale' => $this->locale,
            'host' => $this->host,
            'domain' => $this->domain,
            'active' => $this->active,
        ];
    }
}
