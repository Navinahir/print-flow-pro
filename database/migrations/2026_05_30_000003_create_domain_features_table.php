<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_features', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('domain_setting_id')->constrained('domain_settings')->cascadeOnDelete();
            $table->string('feature_key', 80);
            $table->boolean('is_enabled')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['domain_setting_id', 'feature_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_features');
    }
};
