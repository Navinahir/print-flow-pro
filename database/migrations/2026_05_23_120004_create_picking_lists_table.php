<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('picking_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('upload_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_name');
            $table->string('source_disk', 50)->default('temp');
            $table->string('source_path')->nullable();
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('row_count')->default(0);
            $table->string('output_disk', 50)->nullable();
            $table->string('output_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('picking_lists');
    }
};
