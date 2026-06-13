<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Contracts\Domain\DomainSettingRepositoryInterface;
use App\DTOs\Domain\MerchantDomainConfig;
use App\Models\DomainSetting;
use App\Support\Domains\DomainContext;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DomainConfigurationService
{
    private const CACHE_KEY = 'domain_configuration.merchant_regions';

    private const CACHE_KEY_INFRASTRUCTURE = 'domain_configuration.infrastructure';

    private ?MerchantDomainConfig $current = null;

    public function __construct(
        private readonly DomainSettingRepositoryInterface $repository,
        private readonly CacheRepository $cache,
    ) {}

    public function setCurrent(?MerchantDomainConfig $config): void
    {
        $this->current = $config;
    }

    public function current(): ?MerchantDomainConfig
    {
        return $this->current;
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        $config = $this->current ?? $this->resolveCurrentFromApplicationConfig();

        if ($config === null) {
            return $default;
        }

        if ($key === null) {
            return $config;
        }

        return match ($key) {
            'brand.name' => $config->brandName,
            'brand.tagline' => $config->brandTagline ?? $default,
            'brand.logo' => $config->brandLogo,
            'brand.favicon' => $config->brandFavicon,
            'upload.max_file_size_kb' => $config->uploadMaxFileSizeKb(),
            'upload.max_files_per_job' => $config->uploadMaxFilesPerJob(),
            'preview.width_mm' => $config->previewWidthMm(),
            'preview.height_mm' => $config->previewHeightMm(),
            'preview.safe_zone_inset_mm' => $config->previewSafeZoneInsetMm(),
            'locale.default' => $config->defaultLocale,
            'region_key' => $config->regionKey,
            'country_code' => $config->countryCode,
            default => data_get($config->settings, $key, $default),
        };
    }

    private function resolveCurrentFromApplicationConfig(): ?MerchantDomainConfig
    {
        $regionKey = config('app.region_key');

        if (! is_string($regionKey) || $regionKey === '') {
            return null;
        }

        return $this->configForRegionKey($regionKey);
    }

    public function isFeatureEnabled(string $featureKey, ?string $regionKey = null): bool
    {
        if ($regionKey === null && $this->current !== null) {
            return $this->current->isFeatureEnabled($featureKey);
        }

        $config = $regionKey !== null
            ? $this->configForRegionKey($regionKey)
            : $this->current;

        return $config?->isFeatureEnabled($featureKey) ?? false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function merchantRegionsForRouting(): array
    {
        return $this->rememberMerchantConfigs()
            ->mapWithKeys(fn (MerchantDomainConfig $config): array => [
                $config->regionKey => $config->toRegionArray(),
            ])
            ->all();
    }

    public function configForHost(string $host): ?MerchantDomainConfig
    {
        return $this->rememberMerchantConfigs()
            ->first(fn (MerchantDomainConfig $config): bool => $config->host === $host);
    }

    public function configForRegionKey(string $regionKey): ?MerchantDomainConfig
    {
        return $this->rememberMerchantConfigs()
            ->first(fn (MerchantDomainConfig $config): bool => $config->regionKey === $regionKey);
    }

    public function resolveFromContext(DomainContext $context): ?MerchantDomainConfig
    {
        if (! $context->isMerchant() || $context->regionKey === null) {
            return null;
        }

        return $this->configForRegionKey($context->regionKey);
    }

    public function primaryActiveRegionKey(): string
    {
        $active = $this->rememberMerchantConfigs()
            ->first(fn (MerchantDomainConfig $config): bool => $config->isActive);

        if ($active !== null) {
            return $active->regionKey;
        }

        $first = $this->rememberMerchantConfigs()->first();

        return $first?->regionKey ?? 'tw';
    }

    public function forgetCache(): void
    {
        if (! $this->databaseReady()) {
            return;
        }

        try {
            $this->cache->forget(self::CACHE_KEY);
            $this->cache->forget(self::CACHE_KEY_INFRASTRUCTURE);
        } catch (Throwable) {
            // Cache store may be unavailable during install or before migrations.
        }
    }

    public function syncInfrastructureToConfig(): void
    {
        config([
            'domains.marketing.domain' => $this->effectiveMarketingHost(),
            'domains.admin.domain' => $this->effectiveAdminHost(),
            'domains.admin.path_prefix' => $this->adminPathPrefix(),
        ]);
    }

    public function effectiveMarketingHost(): string
    {
        return $this->infrastructureHost(DomainSetting::SURFACE_MARKETING, 'marketing');
    }

    public function effectiveAdminHost(): string
    {
        return $this->infrastructureHost(DomainSetting::SURFACE_ADMIN, 'admin');
    }

    public function adminPathPrefix(): string
    {
        $prefix = $this->rememberInfrastructure()['admin']['path_prefix'] ?? null;

        if (is_string($prefix) && $prefix !== '') {
            return trim($prefix, '/');
        }

        return trim((string) config('domains.admin.path_prefix', 'boss'), '/');
    }

    /**
     * @return array<string, array{host: string, session_cookie: string, path_prefix?: string}>
     */
    public function infrastructureForDisplay(): array
    {
        return $this->rememberInfrastructure();
    }

    private function infrastructureHost(string $surface, string $fallbackKey): string
    {
        $host = $this->rememberInfrastructure()[$fallbackKey]['host'] ?? '';

        if ($host !== '') {
            return $host;
        }

        return match ($surface) {
            DomainSetting::SURFACE_MARKETING => (string) config('domains.marketing.domain', ''),
            DomainSetting::SURFACE_ADMIN => (string) config('domains.admin.domain', ''),
            default => '',
        };
    }

    /**
     * @return array<string, array{host: string, session_cookie: string, path_prefix?: string}>
     */
    private function rememberInfrastructure(): array
    {
        if (! $this->databaseReady()) {
            return $this->fallbackInfrastructure();
        }

        try {
            /** @var array<string, array{host: string, session_cookie: string, path_prefix?: string}> $infrastructure */
            $infrastructure = $this->cache->rememberForever(self::CACHE_KEY_INFRASTRUCTURE, function (): array {
                $settings = $this->repository->allInfrastructureSettings();

                if ($settings->isEmpty()) {
                    return $this->fallbackInfrastructure();
                }

                $mapped = [];

                foreach ($settings as $setting) {
                    $key = $setting->surface;
                    $mapped[$key] = [
                        'host' => $setting->host,
                        'session_cookie' => (string) ($setting->session_cookie ?? ''),
                    ];

                    if ($setting->surface === DomainSetting::SURFACE_ADMIN) {
                        $mapped[$key]['path_prefix'] = (string) ($setting->settings['path_prefix'] ?? config('domains.admin.path_prefix', 'boss'));
                    }
                }

                return array_replace($this->fallbackInfrastructure(), $mapped);
            });

            return $infrastructure;
        } catch (Throwable) {
            return $this->fallbackInfrastructure();
        }
    }

    /**
     * @return array<string, array{host: string, session_cookie: string, path_prefix?: string}>
     */
    private function fallbackInfrastructure(): array
    {
        $fallback = config('domains.fallback_infrastructure', []);

        return [
            'marketing' => [
                'host' => (string) ($fallback['marketing']['host'] ?? config('domains.marketing.domain', '')),
                'session_cookie' => (string) ($fallback['marketing']['session_cookie'] ?? config('domains.marketing.session_cookie', '')),
            ],
            'admin' => [
                'host' => (string) ($fallback['admin']['host'] ?? config('domains.admin.domain', '')),
                'session_cookie' => (string) ($fallback['admin']['session_cookie'] ?? config('domains.admin.session_cookie', '')),
                'path_prefix' => trim((string) ($fallback['admin']['path_prefix'] ?? config('domains.admin.path_prefix', 'boss')), '/'),
            ],
        ];
    }

    /**
     * @return Collection<int, MerchantDomainConfig>
     */
    private function rememberMerchantConfigs(): Collection
    {
        if (! $this->databaseReady()) {
            return $this->fallbackMerchantConfigs();
        }

        try {
            /** @var Collection<int, MerchantDomainConfig> $configs */
            $configs = $this->cache->rememberForever(self::CACHE_KEY, function (): Collection {
                $settings = $this->repository->allMerchantSettings();

                if ($settings->isEmpty()) {
                    return $this->fallbackMerchantConfigs();
                }

                return $settings->map(fn (DomainSetting $setting): MerchantDomainConfig => $this->mapSetting($setting));
            });

            return $configs;
        } catch (Throwable) {
            return $this->fallbackMerchantConfigs();
        }
    }

    private function mapSetting(DomainSetting $setting): MerchantDomainConfig
    {
        $defaultLocale = $setting->defaultLocale();

        /** @var array<string, bool> $features */
        $features = $setting->features
            ->mapWithKeys(fn ($feature): array => [$feature->feature_key => $feature->is_enabled])
            ->all();

        /** @var list<string> $locales */
        $locales = $setting->locales->pluck('locale')->all();

        return new MerchantDomainConfig(
            id: $setting->id,
            regionKey: $setting->region_key,
            host: $setting->host,
            countryCode: $setting->country_code,
            isActive: $setting->is_active,
            sessionCookie: $setting->session_cookie,
            brandName: $setting->brand_name,
            brandTagline: $setting->brand_tagline,
            brandLogo: $setting->brand_logo,
            brandFavicon: $setting->brand_favicon,
            defaultLocale: $defaultLocale?->locale ?? (string) config('app.locale', 'en'),
            locales: $locales,
            features: $features,
            settings: $setting->settings ?? [],
        );
    }

    /**
     * @return Collection<int, MerchantDomainConfig>
     */
    private function fallbackMerchantConfigs(): Collection
    {
        $regions = config('domains.fallback_merchants', []);

        return collect($regions)->map(function (array $region, string $regionKey): MerchantDomainConfig {
            $defaultLocale = (string) ($region['locale'] ?? config('app.locale', 'en'));

            return new MerchantDomainConfig(
                id: 0,
                regionKey: $regionKey,
                host: (string) ($region['domain'] ?? ''),
                countryCode: (string) ($region['country_code'] ?? ''),
                isActive: (bool) ($region['active'] ?? false),
                sessionCookie: $region['session_cookie'] ?? null,
                brandName: (string) config('domains.fallback_brand.name', 'XY Cubic Shopee'),
                brandTagline: config('domains.fallback_brand.tagline'),
                brandLogo: config('domains.fallback_brand.logo'),
                brandFavicon: config('domains.fallback_brand.favicon'),
                defaultLocale: $defaultLocale,
                locales: [$defaultLocale],
                features: (array) ($region['features'] ?? ['uploads' => true]),
                settings: (array) ($region['settings'] ?? config('domains.fallback_settings', [])),
            );
        })->values();
    }

    private function databaseReady(): bool
    {
        try {
            return Schema::hasTable('domain_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
