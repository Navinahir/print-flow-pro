<?php

declare(strict_types=1);

namespace App\DTOs\Domain;

final readonly class MerchantDomainConfig
{
    /**
     * @param  list<string>  $locales
     * @param  array<string, bool>  $features
     * @param  array<string, mixed>  $settings
     */
    public function __construct(
        public int $id,
        public string $regionKey,
        public string $host,
        public string $countryCode,
        public bool $isActive,
        public ?string $sessionCookie,
        public string $brandName,
        public ?string $brandTagline,
        public ?string $brandLogo,
        public ?string $brandFavicon,
        public string $defaultLocale,
        public array $locales,
        public array $features,
        public array $settings,
    ) {}

    public function isFeatureEnabled(string $featureKey): bool
    {
        return (bool) ($this->features[$featureKey] ?? false);
    }

    /**
     * @return mixed
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function uploadMaxFileSizeKb(): int
    {
        return (int) $this->setting('upload.max_file_size_kb', 20480);
    }

    public function uploadMaxFilesPerJob(): int
    {
        return (int) $this->setting('upload.max_files_per_job', 20);
    }

    public function previewWidthMm(): float
    {
        return (float) $this->setting('preview.width_mm', 150);
    }

    public function previewHeightMm(): float
    {
        return (float) $this->setting('preview.height_mm', 100);
    }

    public function previewSafeZoneInsetMm(): float
    {
        return (float) $this->setting('preview.safe_zone_inset_mm', 5);
    }

    /**
     * @return array<string, mixed>
     */
    public function toRegionArray(): array
    {
        return [
            'domain' => $this->host,
            'country_code' => $this->countryCode,
            'locale' => $this->defaultLocale,
            'active' => $this->isActive,
            'session_cookie' => $this->sessionCookie,
        ];
    }
}
