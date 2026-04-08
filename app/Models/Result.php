<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'qa_run_id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function qaRun(): BelongsTo
    {
        return $this->belongsTo(QaRun::class);
    }
}
