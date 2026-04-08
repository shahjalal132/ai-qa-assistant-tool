<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
