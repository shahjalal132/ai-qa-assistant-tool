<?php

namespace App\Jobs;

use App\Models\QaRun;
use App\Models\QaRunLinkProbe;
use App\Models\Setting;
use App\Services\EmbeddedLinkProbeService;
use App\Services\GeminiService;
use App\Services\LinkProbeResult;
use App\Services\PageFetchOutcome;
use App\Services\PageFetchService;
use App\Services\QaSyntheticResultBuilder;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessQA implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 3;

    public int $uniqueFor = 360;

    public function uniqueId(): string
    {
        return (string) $this->qaRunId;
    }

    public function __construct(public int $qaRunId)
    {
        $t = (int) config('qa.process_qa_timeout', 300);
        if ($t > 0) {
            $this->timeout = $t;
        }
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('gemini-api')];
    }

    public function handle(
        GeminiService $gemini,
        PageFetchService $pageFetch,
        EmbeddedLinkProbeService $linkProbe,
        QaSyntheticResultBuilder $synthetic,
    ): void {
        $run = QaRun::query()->with(['prompt', 'reportUrl', 'aiModel'])->findOrFail($this->qaRunId);

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
            $enOut = $pageFetch->fetch($urlRow->english_url);
            $cyOut = $pageFetch->fetch($urlRow->welsh_url);

            if ($pageFetch->isHardFailure($enOut) || $pageFetch->isHardFailure($cyOut)) {
                $this->markFailed($run, $this->formatHardFailureMessage($enOut, $cyOut, $urlRow->english_url, $urlRow->welsh_url, $pageFetch));

                return;
            }

            $schema = $prompt->response_schema ?? [];
            if ($schema === []) {
                $schema = ['type' => 'object'];
            }

            if ($pageFetch->isSoftUnusable($enOut) && $pageFetch->isSoftUnusable($cyOut)) {
                $data = $synthetic->buildAllChecksFail([
                    'en' => $enOut->summaryForMessage(),
                    'cy' => $cyOut->summaryForMessage(),
                ]);
                $data = $gemini->normalizeQaResultAgainstSchema($data, $schema);

                $this->persistCompleted($run, $data, [], []);

                return;
            }

            $enText = $pageFetch->isOk($enOut)
                ? $gemini->stripHtmlForTokens($enOut->body)
                : $this->unavailablePlaceholder('English', $enOut);
            $cyText = $pageFetch->isOk($cyOut)
                ? $gemini->stripHtmlForTokens($cyOut->body)
                : $this->unavailablePlaceholder('Welsh', $cyOut);

            // Log::info('Clean English Text', [$enText]);
            // Log::info('Clean Welsh Text', [$cyText]);

            $probesEn = $pageFetch->isOk($enOut)
                ? $linkProbe->extractAndProbe($enText, $urlRow->english_url)
                : [];
            $probesCy = $pageFetch->isOk($cyOut)
                ? $linkProbe->extractAndProbe($cyText, $urlRow->welsh_url)
                : [];

            $machineBlock = $linkProbe->formatMachineVerifiedBlock($probesEn, $probesCy);

            $dummySetting = Setting::getValue('qa_use_dummy_ai', '');
            $useDummy = $dummySetting === ''
                ? (bool) config('qa.use_dummy_ai', true)
                : $dummySetting === '1';
            $hasKey = (bool) (Setting::getValue('gemini_api_key', '') ?? '');
            $modelId = $run->aiModel?->name;

            if ($useDummy || ! $hasKey) {
                $data = $gemini->dummyAnalyze($prompt, $enText, $cyText, $schema, $machineBlock !== '' ? $machineBlock : null);
            } else {
                $data = $gemini->analyze($prompt, $enText, $cyText, $schema, $modelId, $machineBlock !== '' ? $machineBlock : null);
            }

            $data = $gemini->normalizeQaResultAgainstSchema($data, $schema);
            $data = $linkProbe->mergeDownloadFailuresIntoResult($data, array_merge($probesEn, $probesCy));

            $this->persistCompleted($run, $data, $probesEn, $probesCy);
        } catch (Throwable $e) {
            Log::warning('ProcessQA failed', [
                'qa_run_id' => $run->id,
                'message' => $e->getMessage(),
            ]);
            $this->markFailed($run, $e->getMessage());

            throw $e;
        }
    }

    /**
     * @param  list<LinkProbeResult>  $probesEn
     * @param  list<LinkProbeResult>  $probesCy
     */
    private function persistCompleted(QaRun $run, array $data, array $probesEn, array $probesCy): void
    {
        DB::transaction(function () use ($run, $data, $probesEn, $probesCy): void {
            $run->linkProbes()->delete();
            $run->result()->delete();
            $run->result()->create(['data' => $data]);

            $now = now();
            $batch = [];
            foreach ($probesEn as $r) {
                if ($r->isOk()) {
                    continue;
                }
                $batch[] = [
                    'qa_run_id' => $run->id,
                    'page_side' => 'en',
                    'url' => $r->url,
                    'http_status' => $r->status,
                    'outcome_label' => $r->label,
                    'is_critical' => $r->isCritical,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach ($probesCy as $r) {
                if ($r->isOk()) {
                    continue;
                }
                $batch[] = [
                    'qa_run_id' => $run->id,
                    'page_side' => 'cy',
                    'url' => $r->url,
                    'http_status' => $r->status,
                    'outcome_label' => $r->label,
                    'is_critical' => $r->isCritical,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($batch, 150) as $chunk) {
                if ($chunk !== []) {
                    QaRunLinkProbe::insert($chunk);
                }
            }

            $run->update([
                'status' => 'completed',
                'completed_at' => now(),
                'error_message' => null,
            ]);
        });
    }

    private function unavailablePlaceholder(string $label, PageFetchOutcome $outcome): string
    {
        return '['.$label.' page unavailable for QA — '.$outcome->summaryForMessage().']';
    }

    private function formatHardFailureMessage(
        PageFetchOutcome $enOut,
        PageFetchOutcome $cyOut,
        string $englishUrl,
        string $welshUrl,
        PageFetchService $pageFetch,
    ): string {
        $parts = [];
        if ($pageFetch->isHardFailure($enOut)) {
            $parts[] = 'English URL ('.$englishUrl.'): '.$enOut->summaryForMessage();
        }
        if ($pageFetch->isHardFailure($cyOut)) {
            $parts[] = 'Welsh URL ('.$welshUrl.'): '.$cyOut->summaryForMessage();
        }

        return implode(' ', $parts);
    }

    private function markFailed(QaRun $run, string $message): void
    {
        $run->update([
            'status' => 'failed',
            'error_message' => $message,
            'completed_at' => now(),
        ]);
    }
}
