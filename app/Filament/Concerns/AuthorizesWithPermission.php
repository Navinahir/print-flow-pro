<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

trait AuthorizesWithPermission
{
    protected static function authorized(string $permission): bool
    {
        $user = auth()->user();

        return $user !== null && $user->can($permission);
    }
}
