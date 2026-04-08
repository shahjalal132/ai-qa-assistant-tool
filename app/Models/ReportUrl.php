<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ReportUrl extends Model
{
    protected $fillable = [
        'csv_upload_batch_id',
        'english_url',
        'welsh_url',
        'metadata',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function csvUploadBatch(): BelongsTo
    {
        return $this->belongsTo(CsvUploadBatch::class);
    }

    public function qaRuns(): HasMany
    {
        return $this->hasMany(QaRun::class);
    }

    /**
     * Bulk insert without Eloquent casts. Encodes metadata as JSON for the database.
     *
     * @param  list<array{csv_upload_batch_id: int, english_url: string, welsh_url: string, metadata?: array|null, status?: string}>  $rows
     */
    public static function insertImportChunks(array $rows, int $chunkSize = 500, ?Carbon $at = null): void
    {
        if ($rows === []) {
            return;
        }

        $now = $at ?? now();
        $dbRows = [];
        foreach ($rows as $r) {
            $metadata = $r['metadata'] ?? null;
            $dbRows[] = [
                'csv_upload_batch_id' => $r['csv_upload_batch_id'],
                'english_url' => $r['english_url'],
                'welsh_url' => $r['welsh_url'],
                'metadata' => ($metadata !== null && $metadata !== []) ? json_encode($metadata) : null,
                'status' => $r['status'] ?? 'pending',
                'error_message' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($dbRows, $chunkSize) as $chunk) {
            self::query()->insert($chunk);
        }
    }
}
