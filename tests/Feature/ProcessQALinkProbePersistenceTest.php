<?php

use App\Jobs\ProcessQA;
use App\Models\AiModel;
use App\Models\CsvUploadBatch;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\QaRunLinkProbe;
use App\Models\ReportUrl;
use App\Models\User;
use App\Services\EmbeddedLinkProbeService;
use App\Services\GeminiService;
use App\Services\PageFetchService;
use App\Services\QaSyntheticResultBuilder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('process qa persists link probes and csv lists inaccessible url', function () {
    Http::fake(function (Request $request) {
        $url = $request->url();
        $method = $request->method();

        if (str_contains($url, 'en-link.test') && $method === 'GET') {
            return Http::response(
                '<html><body><main><a class="nhsuk-button" href="https://en-link.test/uploads/report.docx">DL</a></main></body></html>',
                200,
                ['Content-Type' => 'text/html']
            );
        }

        if (str_contains($url, 'cy-link.test') && $method === 'GET') {
            return Http::response('<html><body><main><p>Cymraeg</p></main></body></html>', 200, ['Content-Type' => 'text/html']);
        }

        if (str_contains($url, 'report.docx') && $method === 'HEAD') {
            return Http::response('', 404);
        }

        return Http::response('unexpected', 500);
    });

    $user = User::factory()->create();
    $batch = CsvUploadBatch::query()->create([
        'filename' => 't.csv',
        'user_id' => $user->id,
        'total_rows' => 1,
    ]);

    $reportUrl = ReportUrl::query()->create([
        'csv_upload_batch_id' => $batch->id,
        'english_url' => 'https://en-link.test/page',
        'welsh_url' => 'https://cy-link.test/page',
        'metadata' => null,
        'status' => 'pending',
    ]);

    $prompt = Prompt::query()->create([
        'title' => 'Link probe prompt',
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
    expect($run->status)->toBe('completed');

    $probes = QaRunLinkProbe::query()->where('qa_run_id', $run->id)->get();
    expect($probes)->toHaveCount(1)
        ->and($probes[0]->page_side)->toBe('en')
        ->and($probes[0]->http_status)->toBe(404)
        ->and($probes[0]->outcome_label)->toBe('not_found')
        ->and($probes[0]->is_critical)->toBeTrue();

    $response = $this->actingAs($user)->get(route('results.export'));
    $response->assertOk();
    $csv = $response->streamedContent();
    expect($csv)->toContain('https://en-link.test/uploads/report.docx')
        ->and($csv)->toContain('not_found');
});
