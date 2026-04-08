<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qa_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_id')->constrained('prompts')->cascadeOnDelete();
            $table->foreignId('report_url_id')->constrained('report_urls')->cascadeOnDelete();
            $table->string('status', 32)->default('pending');
            $table->text('error_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['prompt_id', 'report_url_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_runs');
    }
};
