<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csv_upload_batch_id')->nullable()->constrained('csv_upload_batches')->cascadeOnDelete();
            $table->string('english_url');
            $table->string('welsh_url');
            $table->json('metadata')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_urls');
    }
};
