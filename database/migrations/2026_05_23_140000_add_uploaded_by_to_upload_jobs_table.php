<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upload_jobs', function (Blueprint $table) {
            $table->foreignId('uploaded_by')
                ->nullable()
                ->after('merchant_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('upload_jobs')->whereNotNull('user_id')->update([
            'uploaded_by' => DB::raw('user_id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('upload_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uploaded_by');
        });
    }
};
