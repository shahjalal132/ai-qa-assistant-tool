<?php

namespace Database\Seeders;

use App\Models\AiModel;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\ReportUrl;
use App\Models\Result;
use Illuminate\Database\Seeder;

class QaRunSeeder extends Seeder
{
    public function run(): void
    {
        $prompt = Prompt::query()->where('title', 'NHS QA auditor (EN/CY)')->first();
        if (! $prompt) {
            return;
        }

        $aiModel = AiModel::query()->where('is_default', true)->first() ?? AiModel::query()->first();
        if (! $aiModel) {
            return;
        }

        $urls = ReportUrl::query()->orderBy('id')->limit(2)->get();
        if ($urls->isEmpty()) {
            return;
        }

        $first = $urls->first();
        QaRun::query()->firstOrCreate(
            ['prompt_id' => $prompt->id, 'report_url_id' => $first->id],
            ['ai_model_id' => $aiModel->id, 'status' => 'pending', 'is_active' => true]
        );

        if ($urls->count() > 1) {
            $second = $urls->skip(1)->first();
            $runDone = QaRun::query()->updateOrCreate(
                ['prompt_id' => $prompt->id, 'report_url_id' => $second->id],
                [
                    'ai_model_id' => $aiModel->id,
                    'status' => 'completed',
                    'is_active' => true,
                    'error_message' => null,
                    'started_at' => now(),
                    'completed_at' => now(),
                ]
            );

            $demoData = [
                'content_match' => ['pass' => true, 'reason' => 'Seeded demo result.'],
                'h1_match' => ['pass' => true, 'reason' => 'Seeded.'],
                'format_match' => ['pass' => true, 'reason' => 'Seeded.'],
                'author_match' => ['pass' => true, 'reason' => 'Seeded.'],
                'nhsuk_tag_match' => ['pass' => true, 'reason' => 'Seeded.'],
                'report_download_match' => ['pass' => true, 'reason' => 'Seeded.'],
                'welsh_doc_language' => ['pass' => true, 'reason' => 'Seeded.'],
                'alt_text_check' => ['pass' => true, 'reason' => 'Seeded.'],
                'broken_links' => 'Seeded: none checked.',
            ];

            Result::query()->updateOrCreate(
                ['qa_run_id' => $runDone->id],
                ['data' => $demoData]
            );
        }
    }
}
