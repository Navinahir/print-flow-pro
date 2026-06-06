<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('upload_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pdf_upload_id')->nullable()->constrained()->nullOnDelete();
            $table->string('country_code', 2);
            $table->string('module', 50);
            $table->string('status', 30);
            $table->unsignedSmallInteger('source_page_number')->default(1);
            $table->string('output_disk', 20)->default('temp');
            $table->string('output_path')->nullable();
            $table->decimal('source_width_mm', 8, 2)->nullable();
            $table->decimal('source_height_mm', 8, 2)->nullable();
            $table->string('source_orientation', 20)->nullable();
            $table->decimal('output_width_mm', 8, 2)->default(150);
            $table->decimal('output_height_mm', 8, 2)->default(100);
            $table->string('checksum', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('shredded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
            $table->index(['upload_job_id', 'source_page_number']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
