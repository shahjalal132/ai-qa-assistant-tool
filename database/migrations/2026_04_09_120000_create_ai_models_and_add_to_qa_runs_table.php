<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('note')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('qa_runs', function (Blueprint $table) {
            $table->foreignId('ai_model_id')->nullable()->after('report_url_id')->constrained('ai_models')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('qa_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ai_model_id');
        });

        Schema::dropIfExists('ai_models');
    }
};
