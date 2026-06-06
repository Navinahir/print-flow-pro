<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('merchants', 'email')) {
            return;
        }

        Schema::table('merchants', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropColumn('email');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('merchants', 'email')) {
            return;
        }

        Schema::table('merchants', function (Blueprint $table) {
            $table->string('email')->nullable()->after('shop_name');
            $table->index('email');
        });
    }
};
