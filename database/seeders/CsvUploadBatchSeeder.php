<?php

namespace Database\Seeders;

use App\Models\CsvUploadBatch;
use App\Models\ReportUrl;
use App\Models\User;
use Illuminate\Database\Seeder;

class CsvUploadBatchSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();
        if (! $user) {
            return;
        }

        $batch = CsvUploadBatch::query()->create([
            'filename' => 'demo-seed.csv',
            'user_id' => $user->id,
            'total_rows' => 0,
        ]);

        $pairs = [
            [
                'english_url' => 'https://example.com',
                'welsh_url' => 'https://example.com',
                'metadata' => ['source' => 'seed'],
            ],
            [
                'english_url' => 'https://httpbin.org/html',
                'welsh_url' => 'https://httpbin.org/html',
                'metadata' => ['source' => 'seed'],
            ],
        ];

        foreach ($pairs as $pair) {
            ReportUrl::query()->create([
                'csv_upload_batch_id' => $batch->id,
                'english_url' => $pair['english_url'],
                'welsh_url' => $pair['welsh_url'],
                'metadata' => $pair['metadata'],
                'status' => 'pending',
            ]);
        }

        $batch->update(['total_rows' => count($pairs)]);
    }
}
