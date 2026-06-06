<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DomainFeature;
use App\Models\DomainLocale;
use App\Models\DomainSetting;
use App\Services\Domain\DomainConfigurationService;
use Illuminate\Database\Seeder;

class DomainSettingSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const PRINTING_FEATURES = [
        'printing_order_details',
        'printing_logistics_labels',
        'printing_picking_list',
        'printing_delivery_labels',
    ];

    public function run(): void
    {
        $this->seedInfrastructure();

        $definitions = config('domains.fallback_merchants', []);

        foreach ($definitions as $regionKey => $definition) {
            $setting = DomainSetting::query()->updateOrCreate(
                ['region_key' => (string) $regionKey],
                [
                    'host' => (string) ($definition['domain'] ?? ''),
                    'country_code' => (string) ($definition['country_code'] ?? ''),
                    'surface' => DomainSetting::SURFACE_MERCHANT,
                    'is_active' => (bool) ($definition['active'] ?? false),
                    'session_cookie' => $definition['session_cookie'] ?? null,
                    'brand_name' => (string) ($definition['brand_name'] ?? config('domains.fallback_brand.name')),
                    'brand_tagline' => $definition['brand_tagline'] ?? config('domains.fallback_brand.tagline'),
                    'brand_logo' => $definition['brand_logo'] ?? config('domains.fallback_brand.logo'),
                    'brand_favicon' => $definition['brand_favicon'] ?? config('domains.fallback_brand.favicon'),
                    'settings' => array_merge(
                        config('domains.fallback_settings', []),
                        (array) ($definition['settings'] ?? []),
                    ),
                    'sort_order' => (int) ($definition['sort_order'] ?? 0),
                ],
            );

            $this->seedLocales($setting, $definition);
            $this->seedFeatures($setting, $definition);
        }

        app(DomainConfigurationService::class)->forgetCache();
    }

    private function seedInfrastructure(): void
    {
        $definitions = config('domains.fallback_infrastructure', []);

        foreach ($definitions as $surface => $definition) {
            if (! is_array($definition)) {
                continue;
            }

            $settings = null;

            if ($surface === DomainSetting::SURFACE_ADMIN) {
                $settings = [
                    'path_prefix' => trim((string) ($definition['path_prefix'] ?? 'boss'), '/'),
                ];
            }

            DomainSetting::query()->updateOrCreate(
                ['region_key' => (string) $surface],
                [
                    'surface' => (string) $surface,
                    'host' => (string) ($definition['host'] ?? ''),
                    'country_code' => (string) ($definition['country_code'] ?? '--'),
                    'is_active' => true,
                    'session_cookie' => $definition['session_cookie'] ?? null,
                    'brand_name' => (string) config('app.name', 'XY Cubic Shopee'),
                    'brand_tagline' => null,
                    'brand_logo' => null,
                    'brand_favicon' => null,
                    'settings' => $settings,
                    'sort_order' => $surface === DomainSetting::SURFACE_MARKETING ? 0 : 100,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function seedLocales(DomainSetting $setting, array $definition): void
    {
        /** @var list<array{locale: string, label: string, is_default?: bool}> $locales */
        $locales = $definition['locales'] ?? [[
            'locale' => (string) ($definition['locale'] ?? config('app.locale', 'en')),
            'label' => (string) ($definition['locale_label'] ?? 'English'),
            'is_default' => true,
        ]];

        foreach ($locales as $locale) {
            DomainLocale::query()->updateOrCreate(
                [
                    'domain_setting_id' => $setting->id,
                    'locale' => $locale['locale'],
                ],
                [
                    'label' => $locale['label'],
                    'is_default' => (bool) ($locale['is_default'] ?? false),
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function seedFeatures(DomainSetting $setting, array $definition): void
    {
        $features = array_merge(
            ['uploads' => true],
            (array) ($definition['features'] ?? []),
        );

        foreach (self::PRINTING_FEATURES as $printingFeature) {
            $features[$printingFeature] = $features[$printingFeature] ?? false;
        }

        foreach ($features as $featureKey => $enabled) {
            DomainFeature::query()->updateOrCreate(
                [
                    'domain_setting_id' => $setting->id,
                    'feature_key' => (string) $featureKey,
                ],
                [
                    'is_enabled' => (bool) $enabled,
                    'config' => null,
                ],
            );
        }
    }
}
