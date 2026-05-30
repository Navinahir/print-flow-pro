<?php

declare(strict_types=1);

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 32)
                ->default(RoleEnum::Merchant->value)
                ->after('password')
                ->index();
        });

        $this->backfillRolesFromSpatie();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }

    private function backfillRolesFromSpatie(): void
    {
        $roleTable = config('permission.table_names.roles', 'roles');
        $pivotTable = config('permission.table_names.model_has_roles', 'model_has_roles');
        $modelKey = config('permission.column_names.model_morph_key', 'model_id');

        $legacyMap = [
            'super_admin' => RoleEnum::Admin->value,
            'regional_partner' => RoleEnum::Merchant->value,
            'partner' => RoleEnum::Merchant->value,
            'boss' => RoleEnum::Admin->value,
        ];

        $users = DB::table('users')->select('id')->orderBy('id');

        foreach ($users->cursor() as $user) {
            $spatieRole = DB::table($pivotTable)
                ->join($roleTable, "{$roleTable}.id", '=', "{$pivotTable}.role_id")
                ->where("{$pivotTable}.model_type", 'App\\Models\\User')
                ->where("{$pivotTable}.{$modelKey}", $user->id)
                ->orderBy("{$pivotTable}.role_id")
                ->value("{$roleTable}.name");

            if ($spatieRole === null) {
                continue;
            }

            $role = $legacyMap[$spatieRole] ?? $spatieRole;

            if (! in_array($role, RoleEnum::values(), true)) {
                $role = RoleEnum::Merchant->value;
            }

            DB::table('users')->where('id', $user->id)->update(['role' => $role]);
        }
    }
};
