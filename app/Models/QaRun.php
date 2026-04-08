<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QaRun extends Model
{
    protected $fillable = [
        'prompt_id',
        'report_url_id',
        'status',
        'error_message',
        'is_active',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function reportUrl(): BelongsTo
    {
        return $this->belongsTo(ReportUrl::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }
}
