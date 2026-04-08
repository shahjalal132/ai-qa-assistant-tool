<?php

use App\Models\CsvUploadBatch;
use App\Models\ReportUrl;
use App\Models\User;
use Illuminate\Http\UploadedFile;

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
