<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table): void {
            $table->string('country_code', 2)->default('TW')->after('user_id');
            $table->index('country_code');
        });

        Schema::table('upload_jobs', function (Blueprint $table): void {
            $table->string('country_code', 2)->default('TW')->after('merchant_id');
            $table->index('country_code');
        });

        Schema::table('pdf_uploads', function (Blueprint $table): void {
            if (! Schema::hasColumn('pdf_uploads', 'country_code')) {
                $table->string('country_code', 2)->default('TW')->after('merchant_id');
                $table->index('country_code');
            }
        });

        Schema::table('picking_lists', function (Blueprint $table): void {
            if (! Schema::hasColumn('picking_lists', 'country_code')) {
                $table->string('country_code', 2)->default('TW')->after('merchant_id');
                $table->index('country_code');
            }
        });

        DB::table('merchants')->whereNull('country_code')->update(['country_code' => 'TW']);
        DB::table('upload_jobs')->whereNull('country_code')->update(['country_code' => 'TW']);

        if (Schema::hasColumn('pdf_uploads', 'country_code')) {
            DB::table('pdf_uploads')->whereNull('country_code')->update(['country_code' => 'TW']);
        }

        if (Schema::hasColumn('picking_lists', 'country_code')) {
            DB::table('picking_lists')->whereNull('country_code')->update(['country_code' => 'TW']);
        }

        DB::table('delivery_labels')->whereNull('country_code')->update(['country_code' => 'TW']);
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table): void {
            $table->dropIndex(['country_code']);
            $table->dropColumn('country_code');
        });

        Schema::table('upload_jobs', function (Blueprint $table): void {
            $table->dropIndex(['country_code']);
            $table->dropColumn('country_code');
        });

        if (Schema::hasColumn('pdf_uploads', 'country_code')) {
            Schema::table('pdf_uploads', function (Blueprint $table): void {
                $table->dropIndex(['country_code']);
                $table->dropColumn('country_code');
            });
        }

        if (Schema::hasColumn('picking_lists', 'country_code')) {
            Schema::table('picking_lists', function (Blueprint $table): void {
                $table->dropIndex(['country_code']);
                $table->dropColumn('country_code');
            });
        }
    }
};
