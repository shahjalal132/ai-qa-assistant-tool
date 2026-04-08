<?php

namespace App\Jobs;

use App\Models\QaRun;
use App\Models\Setting;
use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessQA implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public int $uniqueFor = 120;

    public function uniqueId(): string
    {
        return (string) $this->qaRun->id;
    }

    public function __construct(public QaRun $qaRun) {}

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('gemini-api')];
    }

    public function handle(GeminiService $gemini): void
    {
        $run = QaRun::query()->with(['prompt', 'reportUrl'])->findOrFail($this->qaRun->id);

        if (! $run->is_active) {
            return;
        }

        $prompt = $run->prompt;
        if (! $prompt || ! $prompt->is_active) {
            $this->markFailed($run, 'Prompt missing or inactive.');

            return;
        }

        $urlRow = $run->reportUrl;
        if (! $urlRow) {
            $this->markFailed($run, 'Report URL missing.');

            return;
        }

        $run->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $enHtml = $this->fetchBody($urlRow->english_url);
            $cyHtml = $this->fetchBody($urlRow->welsh_url);

            if ($enHtml === '' || $cyHtml === '') {
                throw new \RuntimeException('Empty HTML from one or both URLs (blocked, timeout, or non-200).');
            }

            $enText = $gemini->stripHtmlForTokens($enHtml);
            $cyText = $gemini->stripHtmlForTokens($cyHtml);

            $schema = $prompt->response_schema ?? [];
            if ($schema === []) {
                $schema = ['type' => 'object'];
            }

            $dummySetting = Setting::getValue('qa_use_dummy_ai', '');
            $useDummy = $dummySetting === ''
                ? (bool) config('qa.use_dummy_ai', true)
                : $dummySetting === '1';
            $hasKey = (bool) (Setting::getValue('gemini_api_key', '') ?? '');
            if ($useDummy || ! $hasKey) {
                $data = $gemini->dummyAnalyze($prompt, $enText, $cyText, $schema);
            } else {
                $data = $gemini->analyze($prompt, $enText, $cyText, $schema);
            }

            DB::transaction(function () use ($run, $data): void {
                $run->result()->delete();
                $run->result()->create(['data' => $data]);
                $run->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'error_message' => null,
                ]);
            });
        } catch (Throwable $e) {
            Log::warning('ProcessQA failed', [
                'qa_run_id' => $run->id,
                'message' => $e->getMessage(),
            ]);
            $this->markFailed($run, $e->getMessage());

            throw $e;
        }
    }

    private function markFailed(QaRun $run, string $message): void
    {
        $run->update([
            'status' => 'failed',
            'error_message' => $message,
            'completed_at' => now(),
        ]);
    }

    private function fetchBody(string $url): string
    {
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'AI-QA-Tool/1.0'])
            ->get($url);

        if (! $response->successful()) {
            return '';
        }

        return $response->body();
    }
}
