<?php

namespace App\Models;

use Database\Factories\AiModelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiModel extends Model
{
    /** @use HasFactory<AiModelFactory> */
    use HasFactory;

    protected $table = 'ai_models';

    protected $fillable = [
        'name',
        'note',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function qaRuns(): HasMany
    {
        return $this->hasMany(QaRun::class, 'ai_model_id');
    }
}
