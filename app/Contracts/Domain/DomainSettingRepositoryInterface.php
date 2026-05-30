<?php

declare(strict_types=1);

namespace App\Contracts\Domain;

use App\Models\DomainSetting;
use Illuminate\Support\Collection;

interface DomainSettingRepositoryInterface
{
    /**
     * @return Collection<int, DomainSetting>
     */
    public function allMerchantSettings(): Collection;

    public function findByRegionKey(string $regionKey): ?DomainSetting;

    public function findByHost(string $host): ?DomainSetting;
}
