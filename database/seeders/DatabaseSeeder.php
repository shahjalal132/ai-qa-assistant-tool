<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Shah Jalal',
            'email' => 'jalal@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        $this->call([
            SettingSeeder::class,
            PromptSeeder::class,
            CsvUploadBatchSeeder::class,
            QaRunSeeder::class,
        ]);
    }
}
