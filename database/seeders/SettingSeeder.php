<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setValue('gemini_api_key', '');
        Setting::setValue('qa_use_dummy_ai', '1');
    }
}
