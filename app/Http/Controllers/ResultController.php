<?php

namespace App\Http\Controllers;

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
        $result->load(['qaRun.prompt', 'qaRun.reportUrl']);

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
        $results = Result::query()
            ->whereHas('qaRun.reportUrl.csvUploadBatch', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['qaRun.reportUrl.csvUploadBatch'])
            ->orderBy('id')
            ->get();

        return $this->streamFormattedResultsCsv($results);
    }

    /**
     * @param  Collection<int, Result>|\Illuminate\Database\Eloquent\Collection<int, Result>  $results
     */
    private function streamFormattedResultsCsv($results): StreamedResponse
    {
        $headers = $this->formattedCsvHeaders();

        return response()->streamDownload(function () use ($results, $headers): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fputcsv($out, $headers);
            foreach ($results as $r) {
                $en = trim((string) ($r->qaRun->reportUrl->english_url ?? ''));
                $cy = trim((string) ($r->qaRun->reportUrl->welsh_url ?? ''));
                $data = is_array($r->data) ? $r->data : [];
                $row = [$en, $cy];
                foreach (self::FORMATTED_EXPORT_DATA_KEYS as $key) {
                    $row[] = $this->formatFormattedExportCell($key, $data[$key] ?? null);
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'qa-results-export.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /** @return list<string> */
    private function formattedCsvHeaders(): array
    {
        return [
            __('English URL'),
            __('Welsh URL'),
            __('Content match'),
            __('H1 match'),
            __('Format match'),
            __('Author match'),
            __('NHSUK tag match'),
            __('Report download match'),
            __('Welsh doc language'),
            __('Alt text check'),
            __('Broken links'),
        ];
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

        return $value['pass'] ? 'PASS' : 'FAIL - '.trim($reason);
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

        $results = Result::whereIn('id', $data['ids'])
            ->whereHas('qaRun.reportUrl.csvUploadBatch', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['qaRun.reportUrl.csvUploadBatch'])
            ->get();

        return $this->streamFormattedResultsCsv($results);
    }

    private function removeResult(Result $result): void
    {
        $run = $result->qaRun;
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
