<?php

use App\Jobs\FanOutProcessQaJobs;
use App\Jobs\ProcessQA;
use App\Models\AiModel;
use App\Models\CsvUploadBatch;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\ReportUrl;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('qa run store bulk inserts rows and queues fan-out when dispatch is true', function () {
    Queue::fake();

    $user = User::factory()->create();
    $batch = CsvUploadBatch::query()->create([
        'filename' => 't.csv',
        'user_id' => $user->id,
        'total_rows' => 3,
    ]);
    foreach (range(1, 3) as $i) {
        ReportUrl::query()->create([
            'csv_upload_batch_id' => $batch->id,
            'english_url' => "https://en.example/{$i}",
            'welsh_url' => "https://cy.example/{$i}",
            'metadata' => null,
            'status' => 'pending',
        ]);
    }

    $prompt = Prompt::query()->create([
        'title' => 'Test prompt',
        'system_instruction' => 'Compare pages.',
        'response_schema' => ['type' => 'object'],
        'is_active' => true,
    ]);

    $aiModel = AiModel::factory()->create(['name' => 'gemini-bulk-a']);

    $this->actingAs($user)
        ->postJson(route('qa-runs.store'), [
            'csv_upload_batch_id' => $batch->id,
            'prompt_id' => $prompt->id,
            'ai_model_id' => $aiModel->id,
            'dispatch' => '1',
        ])
        ->assertOk()
        ->assertJsonPath('created', 3)
        ->assertJsonStructure(['redirect', 'message', 'dispatched']);

    expect(QaRun::query()->count())->toBe(3);
    expect(QaRun::query()->where('ai_model_id', $aiModel->id)->count())->toBe(3);

    Queue::assertPushed(FanOutProcessQaJobs::class, function (FanOutProcessQaJobs $job) use ($batch, $prompt, $user) {
        return $job->promptId === $prompt->id
            && $job->csvUploadBatchId === $batch->id
            && $job->userId === $user->id;
    });
    Queue::assertNotPushed(ProcessQA::class);
});

test('qa run store does not queue fan-out when dispatch is false', function () {
    Queue::fake();

    $user = User::factory()->create();
    $batch = CsvUploadBatch::query()->create([
        'filename' => 't.csv',
        'user_id' => $user->id,
        'total_rows' => 1,
    ]);
    ReportUrl::query()->create([
        'csv_upload_batch_id' => $batch->id,
        'english_url' => 'https://en.example/1',
        'welsh_url' => 'https://cy.example/1',
        'metadata' => null,
        'status' => 'pending',
    ]);

    $prompt = Prompt::query()->create([
        'title' => 'Test prompt 2',
        'system_instruction' => 'Compare.',
        'response_schema' => null,
        'is_active' => true,
    ]);

    $aiModel = AiModel::factory()->create(['name' => 'gemini-bulk-b']);

    $this->actingAs($user)
        ->postJson(route('qa-runs.store'), [
            'csv_upload_batch_id' => $batch->id,
            'prompt_id' => $prompt->id,
            'ai_model_id' => $aiModel->id,
            'dispatch' => '0',
        ])
        ->assertOk()
        ->assertJsonPath('created', 1)
        ->assertJsonPath('dispatched', false);

    Queue::assertNotPushed(FanOutProcessQaJobs::class);
});

test('qa run store creates zero new rows when runs already exist', function () {
    Queue::fake();

    $user = User::factory()->create();
    $batch = CsvUploadBatch::query()->create([
        'filename' => 't.csv',
        'user_id' => $user->id,
        'total_rows' => 1,
    ]);
    $url = ReportUrl::query()->create([
        'csv_upload_batch_id' => $batch->id,
        'english_url' => 'https://en.example/1',
        'welsh_url' => 'https://cy.example/1',
        'metadata' => null,
        'status' => 'pending',
    ]);

    $prompt = Prompt::query()->create([
        'title' => 'Test prompt 3',
        'system_instruction' => 'Compare.',
        'response_schema' => null,
        'is_active' => true,
    ]);

    $aiModel = AiModel::factory()->create(['name' => 'gemini-bulk-c']);

    QaRun::query()->create([
        'prompt_id' => $prompt->id,
        'report_url_id' => $url->id,
        'ai_model_id' => $aiModel->id,
        'status' => 'pending',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->postJson(route('qa-runs.store'), [
            'csv_upload_batch_id' => $batch->id,
            'prompt_id' => $prompt->id,
            'ai_model_id' => $aiModel->id,
            'dispatch' => '0',
        ])
        ->assertOk()
        ->assertJsonPath('created', 0);

    expect(QaRun::query()->count())->toBe(1);
});
