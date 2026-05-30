<?php

declare(strict_types=1);

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_TO_V24 = [
        'super_admin' => 'admin',
        'boss' => 'admin',
        'partner' => 'merchant',
        'regional_partner' => 'merchant',
    ];

    public function up(): void
    {
        foreach (self::LEGACY_TO_V24 as $legacy => $target) {
            DB::table('users')
                ->where('role', $legacy)
                ->update(['role' => $target]);
        }

        DB::table('users')
            ->whereNotIn('role', RoleEnum::values())
            ->update(['role' => RoleEnum::Merchant->value]);
    }

    public function down(): void
    {
        // Role simplification is not reversible without external data.
    }
};
