<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qa_run_link_probes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qa_run_id')->constrained('qa_runs')->cascadeOnDelete();
            $table->string('page_side', 8);
            $table->text('url');
            $table->unsignedSmallInteger('http_status');
            $table->string('outcome_label', 32);
            $table->boolean('is_critical')->default(false);
            $table->timestamps();

            $table->index(['qa_run_id', 'outcome_label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_run_link_probes');
    }
};
