<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('region_key', 20)->unique();
            $table->string('host');
            $table->string('country_code', 2);
            $table->string('surface', 20)->default('merchant');
            $table->boolean('is_active')->default(false);
            $table->string('session_cookie')->nullable();
            $table->string('brand_name');
            $table->string('brand_tagline')->nullable();
            $table->string('brand_logo')->nullable();
            $table->string('brand_favicon')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique('host');
            $table->index(['surface', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_settings');
    }
};
