<?php

use App\Models\AiModel;
use App\Models\CsvUploadBatch;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\ReportUrl;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('csv upload accepts utf-8 bom on header row', function () {
    $user = User::factory()->create();
    $csv = "\xEF\xBB\xBFenglish_url,welsh_url\nhttps://en.example/a,https://cy.example/a\n";
    $file = UploadedFile::fake()->createWithContent('bom.csv', $csv);

    $this->actingAs($user)
        ->post(route('csv-upload-batches.store'), ['csv' => $file])
        ->assertRedirect();

    expect(ReportUrl::query()->count())->toBe(1);
});

test('csv upload bulk inserts report urls', function () {
    $user = User::factory()->create();
    $csv = "english_url,welsh_url,title\nhttps://en.example/a,https://cy.example/a,First\nhttps://en.example/b,https://cy.example/b,Second\n";
    $file = UploadedFile::fake()->createWithContent('pairs.csv', $csv);

    $this->actingAs($user)
        ->post(route('csv-upload-batches.store'), ['csv' => $file])
        ->assertRedirect();

    expect(ReportUrl::query()->count())->toBe(2);
    $batch = CsvUploadBatch::query()->first();
    expect($batch)->not->toBeNull()
        ->and($batch->total_rows)->toBe(2);

    $first = ReportUrl::query()->orderBy('id')->first();
    expect($first->english_url)->toBe('https://en.example/a')
        ->and($first->metadata)->toBe(['title' => 'First']);
});

test('batch items json includes display_status and optional status filter', function () {
    $user = User::factory()->create();
    $batch = CsvUploadBatch::query()->create([
        'filename' => 't.csv',
        'user_id' => $user->id,
        'total_rows' => 2,
    ]);

    $urlNoRun = ReportUrl::query()->create([
        'csv_upload_batch_id' => $batch->id,
        'english_url' => 'https://en.example/norun',
        'welsh_url' => 'https://cy.example/norun',
        'metadata' => null,
        'status' => 'pending',
    ]);

    $urlWithRun = ReportUrl::query()->create([
        'csv_upload_batch_id' => $batch->id,
        'english_url' => 'https://en.example/done',
        'welsh_url' => 'https://cy.example/done',
        'metadata' => null,
        'status' => 'pending',
    ]);

    $prompt = Prompt::query()->create([
        'title' => 'Items API prompt',
        'system_instruction' => 'Test.',
        'response_schema' => ['type' => 'object'],
        'is_active' => true,
    ]);

    $model = AiModel::factory()->create();

    QaRun::query()->create([
        'prompt_id' => $prompt->id,
        'report_url_id' => $urlWithRun->id,
        'ai_model_id' => $model->id,
        'status' => 'completed',
        'error_message' => null,
        'is_active' => true,
        'started_at' => null,
        'completed_at' => now(),
    ]);

    $all = $this->actingAs($user)
        ->getJson(route('csv-upload-batches.items', $batch))
        ->assertOk()
        ->json('data');

    expect($all)->toHaveCount(2);
    $byId = collect($all)->keyBy('id');
    expect($byId[$urlNoRun->id]['display_status'])->toBe('pending')
        ->and($byId[$urlWithRun->id]['display_status'])->toBe('completed');

    $pendingOnly = $this->actingAs($user)
        ->getJson(route('csv-upload-batches.items', ['batch' => $batch, 'status' => 'pending']))
        ->assertOk()
        ->json('data');
    expect($pendingOnly)->toHaveCount(1)
        ->and($pendingOnly[0]['id'])->toBe($urlNoRun->id);

    $completedOnly = $this->actingAs($user)
        ->getJson(route('csv-upload-batches.items', ['batch' => $batch, 'status' => 'completed']))
        ->assertOk()
        ->json('data');
    expect($completedOnly)->toHaveCount(1)
        ->and($completedOnly[0]['id'])->toBe($urlWithRun->id);
});
