<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QaRunLinkProbe extends Model
{
    protected $fillable = [
        'qa_run_id',
        'page_side',
        'url',
        'http_status',
        'outcome_label',
        'is_critical',
    ];

    protected function casts(): array
    {
        return [
            'is_critical' => 'boolean',
        ];
    }

    public function qaRun(): BelongsTo
    {
        return $this->belongsTo(QaRun::class);
    }
}
