<?php

use App\Models\AiModel;
use App\Models\CsvUploadBatch;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\ReportUrl;
use App\Models\User;

test('authenticated user can create an ai model', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('models.store'), [
            'name' => 'gemini-2.0-flash',
            'note' => 'Fast runs',
            'is_default' => '1',
        ])
        ->assertRedirect(route('models.index'))
        ->assertSessionHas('status');

    $m = AiModel::query()->where('name', 'gemini-2.0-flash')->first();
    expect($m)->not->toBeNull();
    expect($m->note)->toBe('Fast runs');
    expect($m->is_default)->toBeTrue();
});

test('setting a new default clears the previous default', function () {
    $user = User::factory()->create();
    AiModel::factory()->create(['name' => 'gemini-first', 'is_default' => true]);

    $this->actingAs($user)
        ->post(route('models.store'), [
            'name' => 'gemini-second',
            'is_default' => '1',
        ])
        ->assertRedirect(route('models.index'));

    expect(AiModel::query()->where('name', 'gemini-first')->first()->is_default)->toBeFalse();
    expect(AiModel::query()->where('name', 'gemini-second')->first()->is_default)->toBeTrue();
});

test('update can move default to another model', function () {
    $user = User::factory()->create();
    $a = AiModel::factory()->create(['name' => 'gemini-a', 'is_default' => true]);
    $b = AiModel::factory()->create(['name' => 'gemini-b', 'is_default' => false]);

    $this->actingAs($user)
        ->patch(route('models.update', $b), [
            'name' => 'gemini-b',
            'is_default' => '1',
        ])
        ->assertRedirect(route('models.index'));

    expect($a->fresh()->is_default)->toBeFalse();
    expect($b->fresh()->is_default)->toBeTrue();
});

test('cannot delete ai model when qa runs reference it', function () {
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
        'title' => 'P',
        'system_instruction' => 'x',
        'response_schema' => null,
        'is_active' => true,
    ]);
    $aiModel = AiModel::factory()->create(['name' => 'gemini-in-use']);
    QaRun::query()->create([
        'prompt_id' => $prompt->id,
        'report_url_id' => $url->id,
        'ai_model_id' => $aiModel->id,
        'status' => 'pending',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->delete(route('models.destroy', $aiModel))
        ->assertRedirect(route('models.index'))
        ->assertSessionHasErrors('delete');

    expect(AiModel::query()->whereKey($aiModel->id)->exists())->toBeTrue();
});

test('can delete unused ai model', function () {
    $user = User::factory()->create();
    $aiModel = AiModel::factory()->create(['name' => 'gemini-unused']);

    $this->actingAs($user)
        ->delete(route('models.destroy', $aiModel))
        ->assertRedirect(route('models.index'))
        ->assertSessionHas('status');

    expect(AiModel::query()->whereKey($aiModel->id)->exists())->toBeFalse();
});

test('model name is normalized to lowercase on store', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('models.store'), [
            'name' => 'Gemini-UPPER-99',
            'is_default' => '0',
        ])
        ->assertRedirect(route('models.index'));

    expect(AiModel::query()->where('name', 'gemini-upper-99')->exists())->toBeTrue();
});
