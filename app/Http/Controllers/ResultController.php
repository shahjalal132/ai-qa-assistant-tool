<?php

namespace App\Http\Controllers;

use App\Models\QaRun;
use App\Models\QaRunLinkProbe;
use App\Models\Result;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultController extends Controller
{
    /** @var list<string> Keys in `result.data`, matching formatted NHS export column order after URL columns. */
    private const FORMATTED_EXPORT_DATA_KEYS = [
        'content_match',
        'h1_match',
        'format_match',
        'author_match',
        'nhsuk_tag_match',
        'report_download_match',
        'welsh_doc_language',
        'alt_text_check',
        'broken_links',
    ];

    public function index(Request $request): View
    {
        $results = Result::query()
            ->whereHas('qaRun.reportUrl.csvUploadBatch', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['qaRun.prompt', 'qaRun.reportUrl.csvUploadBatch'])
            ->latest()
            ->paginate(20);

        return view('results.index', compact('results'));
    }

    public function show(Result $result): View
    {
        $this->authorizeResult($result);
        $result->load(['qaRun.prompt', 'qaRun.reportUrl', 'qaRun.linkProbes']);

        return view('results.show', compact('result'));
    }

    public function download(Result $result): Response
    {
        $this->authorizeResult($result);
        $payload = json_encode($result->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $name = 'qa-result-'.$result->id.'.json';

        return response($payload, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$name.'"',
        ]);
    }

    public function export(): StreamedResponse
    {
        $qaRuns = $this->qaRunsForUserExport();

        return $this->streamFormattedQaRunsCsv($qaRuns);
    }

    /**
     * Completed and failed QA runs for the current user (via batch ownership).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, QaRun>
     */
    private function qaRunsForUserExport()
    {
        return QaRun::query()
            ->whereHas('reportUrl.csvUploadBatch', fn ($q) => $q->where('user_id', auth()->id()))
            ->whereIn('status', ['completed', 'failed'])
            ->with(['reportUrl', 'result', 'linkProbes'])
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, QaRun>|\Illuminate\Database\Eloquent\Collection<int, QaRun>  $qaRuns
     */
    private function streamFormattedQaRunsCsv($qaRuns): StreamedResponse
    {
        $headers = $this->formattedCsvHeaders();

        return response()->streamDownload(function () use ($qaRuns, $headers): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fputcsv($out, $headers);
            foreach ($qaRuns as $run) {
                fputcsv($out, $this->buildFormattedCsvRow($run));
            }
            fclose($out);
        }, 'qa-results-export.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildFormattedCsvRow(QaRun $run): array
    {
        $en = trim((string) ($run->reportUrl->english_url ?? ''));
        $cy = trim((string) ($run->reportUrl->welsh_url ?? ''));
        $na = __('N/A — run failed');

        $row = [
            $en,
            $cy,
            $run->status,
            $run->status === 'failed' ? (string) ($run->error_message ?? '') : '',
        ];

        if ($run->status === 'completed' && $run->result) {
            $data = is_array($run->result->data) ? $run->result->data : [];
            foreach (self::FORMATTED_EXPORT_DATA_KEYS as $key) {
                $row[] = $this->formatFormattedExportCell($key, $data[$key] ?? null);
            }
            $row[] = $this->formatInaccessibleLinksCell($run);
        } else {
            foreach (self::FORMATTED_EXPORT_DATA_KEYS as $_) {
                $row[] = $na;
            }
            $row[] = $na;
        }

        return $row;
    }

    /** @return list<string> */
    private function formattedCsvHeaders(): array
    {
        return [
            __('English URL'),
            __('Welsh URL'),
            __('Run status'),
            __('Error / fetch notes'),
            __('Content match'),
            __('H1 match'),
            __('Format match'),
            __('Author match'),
            __('NHSUK tag match'),
            __('Report download match'),
            __('Welsh doc language'),
            __('Alt text check'),
            __('Broken links'),
            __('Inaccessible links (machine check)'),
        ];
    }

    private function formatInaccessibleLinksCell(QaRun $run): string
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, QaRunLinkProbe> $probes */
        $probes = $run->linkProbes->filter(fn (QaRunLinkProbe $p) => $p->outcome_label !== 'reachable');
        if ($probes->isEmpty()) {
            return __('None (all checked links reachable).');
        }

        return $probes
            ->map(fn (QaRunLinkProbe $p) => $p->url.' | HTTP '.$p->http_status.' | '.$p->outcome_label.($p->is_critical ? ' [critical]' : ''))
            ->implode('; ');
    }

    private function formatFormattedExportCell(string $dataKey, mixed $value): string
    {
        $missing = __('Not provided in audit output (incomplete response; re-run QA or review manually).');

        if ($dataKey === 'broken_links') {
            if ($value === null) {
                return $missing;
            }
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            return $missing;
        }

        if (! is_array($value)) {
            return $missing;
        }

        $passOk = array_key_exists('pass', $value) && is_bool($value['pass']);
        $reason = $value['reason'] ?? null;
        $reasonOk = is_string($reason) && trim($reason) !== '';

        if (! $passOk || ! $reasonOk) {
            return $missing;
        }

        return $value['pass'] ? 'PASS' : trim($reason);
    }

    public function destroy(Result $result): RedirectResponse
    {
        $this->authorizeResult($result);
        $this->removeResult($result);

        return redirect()->route('results.index')->with('status', __('Result removed; run reset to pending.'));
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:results,id'],
        ]);

        $results = Result::whereIn('id', $data['ids'])
            ->whereHas('qaRun.reportUrl.csvUploadBatch', fn ($q) => $q->where('user_id', auth()->id()))
            ->get();

        /** @var Result $result */
        foreach ($results as $result) {
            $this->removeResult($result);
        }

        return redirect()->route('results.index')->with('status', __('Selected results removed; runs reset to pending.'));
    }

    public function bulkExport(Request $request): StreamedResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:results,id'],
        ]);

        $qaRunIds = Result::query()
            ->whereIn('id', $data['ids'])
            ->whereHas('qaRun.reportUrl.csvUploadBatch', fn ($q) => $q->where('user_id', auth()->id()))
            ->pluck('qa_run_id')
            ->unique()
            ->values();

        $qaRuns = QaRun::query()
            ->whereIn('id', $qaRunIds)
            ->with(['reportUrl', 'result', 'linkProbes'])
            ->orderBy('id')
            ->get();

        return $this->streamFormattedQaRunsCsv($qaRuns);
    }

    private function removeResult(Result $result): void
    {
        $run = $result->qaRun;
        $run?->linkProbes()->delete();
        $result->delete();
        $run?->update([
            'status' => 'pending',
            'error_message' => null,
            'completed_at' => null,
        ]);
    }

    private function authorizeResult(Result $result): void
    {
        $result->loadMissing('qaRun.reportUrl.csvUploadBatch');
        $batch = $result->qaRun?->reportUrl?->csvUploadBatch;
        abort_unless($batch && $batch->user_id === auth()->id(), 403);
    }
}
