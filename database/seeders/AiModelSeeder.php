<?php

namespace Database\Seeders;

use App\Models\AiModel;
use Illuminate\Database\Seeder;

class AiModelSeeder extends Seeder
{
    public function run(): void
    {
        if (AiModel::query()->exists()) {
            return;
        }

        AiModel::query()->create([
            'name' => 'gemini-3.1-pro-preview',
            'note' => null,
            'is_default' => true,
        ]);
    }
}
