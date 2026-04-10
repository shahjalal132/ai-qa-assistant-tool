<?php

use App\Jobs\ProcessQA;
use App\Models\AiModel;
use App\Models\CsvUploadBatch;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\ReportUrl;
use App\Models\Result;
use App\Models\User;
use App\Services\EmbeddedLinkProbeService;
use App\Services\GeminiService;
use App\Services\PageFetchService;
use App\Services\QaSyntheticResultBuilder;
use Illuminate\Support\Facades\Http;

test('process qa completes with synthetic result when both pages are soft unusable', function () {
    $err = '<html><head><title>Page not found</title></head><body><h1>Page not found</h1></body></html>';
    Http::fake([
        'https://en-soft.test/*' => Http::response($err, 200, ['Content-Type' => 'text/html']),
        'https://cy-soft.test/*' => Http::response($err, 200, ['Content-Type' => 'text/html']),
    ]);

    $user = User::factory()->create();
    $batch = CsvUploadBatch::query()->create([
        'filename' => 't.csv',
        'user_id' => $user->id,
        'total_rows' => 1,
    ]);

    $reportUrl = ReportUrl::query()->create([
        'csv_upload_batch_id' => $batch->id,
        'english_url' => 'https://en-soft.test/r',
        'welsh_url' => 'https://cy-soft.test/r',
        'metadata' => null,
        'status' => 'pending',
    ]);

    $prompt = Prompt::query()->create([
        'title' => 'Test prompt',
        'system_instruction' => 'Return JSON.',
        'response_schema' => [
            'type' => 'object',
            'properties' => [
                'content_match' => ['type' => 'object', 'properties' => ['pass' => ['type' => 'boolean'], 'reason' => ['type' => 'string']]],
                'h1_match' => ['type' => 'object', 'properties' => ['pass' => ['type' => 'boolean'], 'reason' => ['type' => 'string']]],
                'format_match' => ['type' => 'object', 'properties' => ['pass' => ['type' => 'boolean'], 'reason' => ['type' => 'string']]],
                'author_match' => ['type' => 'object', 'properties' => ['pass' => ['type' => 'boolean'], 'reason' => ['type' => 'string']]],
                'nhsuk_tag_match' => ['type' => 'object', 'properties' => ['pass' => ['type' => 'boolean'], 'reason' => ['type' => 'string']]],
                'report_download_match' => ['type' => 'object', 'properties' => ['pass' => ['type' => 'boolean'], 'reason' => ['type' => 'string']]],
                'welsh_doc_language' => ['type' => 'object', 'properties' => ['pass' => ['type' => 'boolean'], 'reason' => ['type' => 'string']]],
                'alt_text_check' => ['type' => 'object', 'properties' => ['pass' => ['type' => 'boolean'], 'reason' => ['type' => 'string']]],
                'broken_links' => ['type' => 'string'],
            ],
        ],
        'is_active' => true,
    ]);

    $model = AiModel::factory()->create();

    $run = QaRun::query()->create([
        'prompt_id' => $prompt->id,
        'report_url_id' => $reportUrl->id,
        'ai_model_id' => $model->id,
        'status' => 'pending',
        'error_message' => null,
        'is_active' => true,
        'started_at' => null,
        'completed_at' => null,
    ]);

    $job = new ProcessQA($run->id);
    $job->handle(
        app(GeminiService::class),
        app(PageFetchService::class),
        app(EmbeddedLinkProbeService::class),
        app(QaSyntheticResultBuilder::class),
    );

    $run->refresh();
    expect($run->status)->toBe('completed')
        ->and($run->error_message)->toBeNull();

    $result = Result::query()->where('qa_run_id', $run->id)->first();
    expect($result)->not->toBeNull()
        ->and($result->data['content_match']['pass'])->toBeFalse();
});

test('process qa marks failed when english response is empty http error', function () {
    Http::fake([
        'https://en-hard.test/*' => Http::response('', 404),
        'https://cy-hard.test/*' => Http::response('<html><body><main><h1>OK</h1></main></body></html>', 200, ['Content-Type' => 'text/html']),
    ]);

    $user = User::factory()->create();
    $batch = CsvUploadBatch::query()->create([
        'filename' => 't.csv',
        'user_id' => $user->id,
        'total_rows' => 1,
    ]);

    $reportUrl = ReportUrl::query()->create([
        'csv_upload_batch_id' => $batch->id,
        'english_url' => 'https://en-hard.test/r',
        'welsh_url' => 'https://cy-hard.test/r',
        'metadata' => null,
        'status' => 'pending',
    ]);

    $prompt = Prompt::query()->create([
        'title' => 'Test prompt 2',
        'system_instruction' => 'Return JSON.',
        'response_schema' => ['type' => 'object'],
        'is_active' => true,
    ]);

    $model = AiModel::factory()->create();

    $run = QaRun::query()->create([
        'prompt_id' => $prompt->id,
        'report_url_id' => $reportUrl->id,
        'ai_model_id' => $model->id,
        'status' => 'pending',
        'error_message' => null,
        'is_active' => true,
        'started_at' => null,
        'completed_at' => null,
    ]);

    $job = new ProcessQA($run->id);
    $job->handle(
        app(GeminiService::class),
        app(PageFetchService::class),
        app(EmbeddedLinkProbeService::class),
        app(QaSyntheticResultBuilder::class),
    );

    $run->refresh();
    expect($run->status)->toBe('failed')
        ->and($run->error_message)->toContain('English URL');

    expect(Result::query()->where('qa_run_id', $run->id)->count())->toBe(0);
});
