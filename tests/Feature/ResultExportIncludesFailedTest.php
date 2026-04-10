<?php

use App\Models\AiModel;
use App\Models\CsvUploadBatch;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\ReportUrl;
use App\Models\User;

test('results export csv includes failed qa runs with error column', function () {
    $user = User::factory()->create();
    $batch = CsvUploadBatch::query()->create([
        'filename' => 't.csv',
        'user_id' => $user->id,
        'total_rows' => 1,
    ]);

    $reportUrl = ReportUrl::query()->create([
        'csv_upload_batch_id' => $batch->id,
        'english_url' => 'https://en.example/export-fail',
        'welsh_url' => 'https://cy.example/export-fail',
        'metadata' => null,
        'status' => 'pending',
    ]);

    $prompt = Prompt::query()->create([
        'title' => 'Export test prompt',
        'system_instruction' => 'x',
        'response_schema' => ['type' => 'object'],
        'is_active' => true,
    ]);

    $model = AiModel::factory()->create();

    QaRun::query()->create([
        'prompt_id' => $prompt->id,
        'report_url_id' => $reportUrl->id,
        'ai_model_id' => $model->id,
        'status' => 'failed',
        'error_message' => 'English URL (https://en.example/x): HTTP 404',
        'is_active' => true,
        'started_at' => now(),
        'completed_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('results.export'));
    $response->assertOk();

    $csv = $response->streamedContent();
    expect($csv)->toContain('failed')
        ->and($csv)->toContain('English URL (https://en.example/x)')
        ->and($csv)->toContain('https://en.example/export-fail');
});
