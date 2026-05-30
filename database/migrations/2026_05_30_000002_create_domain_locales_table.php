<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_locales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('domain_setting_id')->constrained('domain_settings')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('label');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['domain_setting_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_locales');
    }
};
