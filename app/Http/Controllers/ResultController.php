<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultController extends Controller
{
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
            ->with(['qaRun.prompt', 'qaRun.reportUrl.csvUploadBatch'])
            ->orderBy('id')
            ->get();

        $headers = ['result_id', 'qa_run_id', 'english_url', 'welsh_url', 'prompt_title'];
        $dynamicKeys = [];
        foreach ($results as $r) {
            if (is_array($r->data)) {
                foreach (array_keys($r->data) as $k) {
                    $dynamicKeys[$k] = true;
                }
            }
        }
        $extra = array_keys($dynamicKeys);
        sort($extra);
        $allHeaders = array_merge($headers, $extra);

        return $this->streamCsv($results, $allHeaders, $extra);
    }

    private function streamCsv($results, array $allHeaders, array $extra): StreamedResponse
    {
        return response()->streamDownload(function () use ($results, $allHeaders, $extra): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fputcsv($out, $allHeaders);
            foreach ($results as $r) {
                $row = [
                    $r->id,
                    $r->qa_run_id,
                    $r->qaRun->reportUrl->english_url ?? '',
                    $r->qaRun->reportUrl->welsh_url ?? '',
                    $r->qaRun->prompt->title ?? '',
                ];
                $data = is_array($r->data) ? $r->data : [];
                foreach ($extra as $key) {
                    $row[] = $this->formatResultCsvCell($data[$key] ?? null);
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'qa-results-export.csv', [
            'Content-Type' => 'text/csv',
        ]);
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
            ->with(['qaRun.prompt', 'qaRun.reportUrl.csvUploadBatch'])
            ->get();

        $headers = ['result_id', 'qa_run_id', 'english_url', 'welsh_url', 'prompt_title'];
        $dynamicKeys = [];
        foreach ($results as $r) {
            if (is_array($r->data)) {
                foreach (array_keys($r->data) as $k) {
                    $dynamicKeys[$k] = true;
                }
            }
        }
        $extra = array_keys($dynamicKeys);
        sort($extra);
        $allHeaders = array_merge($headers, $extra);

        return $this->streamCsv($results, $allHeaders, $extra);
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

    /**
     * @param  mixed  $val  JSON value from result.data (scalar or array)
     */
    private function formatResultCsvCell(mixed $val): string
    {
        $missing = __('Not provided in audit output (incomplete response; re-run QA or review manually).');

        if (is_array($val)) {
            $encoded = json_encode($val);

            return is_string($encoded) ? $encoded : $missing;
        }

        if ($val === null) {
            return $missing;
        }

        if (is_string($val) && trim($val) === '') {
            return $missing;
        }

        if (is_bool($val)) {
            return $val ? '1' : '0';
        }

        return (string) $val;
    }
}
