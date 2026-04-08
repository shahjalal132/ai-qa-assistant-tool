<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prompt extends Model
{
    protected $fillable = [
        'title',
        'system_instruction',
        'response_schema',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'response_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function qaRuns(): HasMany
    {
        return $this->hasMany(QaRun::class);
    }
}
