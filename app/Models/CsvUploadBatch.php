<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsvUploadBatch extends Model
{
    protected $fillable = [
        'filename',
        'user_id',
        'total_rows',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportUrls(): HasMany
    {
        return $this->hasMany(ReportUrl::class);
    }
}
