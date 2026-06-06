<?php

declare(strict_types=1);

namespace App\Repositories\Domain;

use App\Contracts\Domain\DomainSettingRepositoryInterface;
use App\Models\DomainSetting;
use Illuminate\Support\Collection;

class DomainSettingRepository implements DomainSettingRepositoryInterface
{
    /**
     * @return Collection<int, DomainSetting>
     */
    public function allMerchantSettings(): Collection
    {
        return DomainSetting::query()
            ->where('surface', DomainSetting::SURFACE_MERCHANT)
            ->with(['locales', 'features'])
            ->orderBy('sort_order')
            ->orderBy('region_key')
            ->get();
    }

    public function findByRegionKey(string $regionKey): ?DomainSetting
    {
        return DomainSetting::query()
            ->where('surface', DomainSetting::SURFACE_MERCHANT)
            ->where('region_key', $regionKey)
            ->with(['locales', 'features'])
            ->first();
    }

    public function findByHost(string $host): ?DomainSetting
    {
        return DomainSetting::query()
            ->where('surface', DomainSetting::SURFACE_MERCHANT)
            ->where('host', $host)
            ->with(['locales', 'features'])
            ->first();
    }

    public function findInfrastructureBySurface(string $surface): ?DomainSetting
    {
        return DomainSetting::query()
            ->where('surface', $surface)
            ->first();
    }

    /**
     * @return Collection<int, DomainSetting>
     */
    public function allInfrastructureSettings(): Collection
    {
        return DomainSetting::query()
            ->whereIn('surface', [
                DomainSetting::SURFACE_MARKETING,
                DomainSetting::SURFACE_ADMIN,
            ])
            ->orderBy('sort_order')
            ->orderBy('region_key')
            ->get();
    }
}
