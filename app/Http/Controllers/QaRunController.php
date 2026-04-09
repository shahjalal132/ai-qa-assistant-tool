<?php

namespace App\Http\Controllers;

use App\Jobs\FanOutProcessQaJobs;
use App\Jobs\ProcessQA;
use App\Models\CsvUploadBatch;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\ReportUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AI QA manager: each {@see QaRun} links one {@see ReportUrl} row to one {@see Prompt}.
 *
 * The same English/Welsh URL pair can have multiple runs (different prompts) without duplicating
 * {@see ReportUrl} records. This controller lists runs, creates runs in bulk for a CSV batch + prompt,
 * toggles whether a run is eligible for the queue ({@see QaRun::$is_active}), retries failed runs,
 * and dispatches {@see ProcessQA} jobs when requested.
 */
class QaRunController extends Controller
{
    public function index(Request $request): View
    {
        $runs = QaRun::query()
            ->whereHas('reportUrl.csvUploadBatch', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['prompt', 'reportUrl.csvUploadBatch'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20);

        return view('qa-runs.index', compact('runs'));
    }

    public function create(): View
    {
        $batches = CsvUploadBatch::query()
            ->where('user_id', auth()->id())
            ->withCount('reportUrls')
            ->latest()
            ->get();

        $prompts = Prompt::query()->where('is_active', true)->orderBy('title')->get();

        return view('qa-runs.create', compact('batches', 'prompts'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'csv_upload_batch_id' => ['required', 'exists:csv_upload_batches,id'],
            'prompt_id' => ['required', 'exists:prompts,id'],
            'dispatch' => ['sometimes', 'boolean'],
            'ids' => ['sometimes', 'array'],
            'ids.*' => ['exists:report_urls,id'],
        ]);

        $batch = CsvUploadBatch::query()->findOrFail($data['csv_upload_batch_id']);
        abort_unless($batch->user_id === auth()->id(), 403);

        $prompt = Prompt::query()->findOrFail($data['prompt_id']);
        abort_unless($prompt->is_active, 422, __('Prompt is inactive.'));

        $query = ReportUrl::query()->where('csv_upload_batch_id', $batch->id);
        if ($request->filled('ids')) {
            $query->whereIn('id', $data['ids']);
        }
        $reportUrlIds = $query->pluck('id');

        $existingReportUrlIds = QaRun::query()
            ->where('prompt_id', $prompt->id)
            ->whereIn('report_url_id', $reportUrlIds)
            ->pluck('report_url_id');

        $missingIds = $reportUrlIds->diff($existingReportUrlIds)->values();
        $now = now();
        $insertRows = $missingIds->map(fn (int $reportUrlId) => [
            'prompt_id' => $prompt->id,
            'report_url_id' => $reportUrlId,
            'status' => 'pending',
            'error_message' => null,
            'is_active' => true,
            'started_at' => null,
            'completed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        foreach (array_chunk($insertRows, 500) as $chunk) {
            if ($chunk !== []) {
                QaRun::query()->insert($chunk);
            }
        }

        $created = $missingIds->count();

        if ($request->boolean('dispatch')) {
            FanOutProcessQaJobs::dispatch($prompt->id, $batch->id, (int) auth()->id())
                ->afterResponse();
        }

        $message = __('Linked runs for this batch and prompt. New rows created: :n. Dispatched pending jobs: :d.', [
            'n' => $created,
            'd' => $request->boolean('dispatch') ? __('yes') : __('no'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('qa-runs.index'),
                'created' => $created,
                'dispatched' => $request->boolean('dispatch'),
            ]);
        }

        return redirect()->route('qa-runs.index')->with('status', $message);
    }

    public function show(QaRun $qaRun): View
    {
        $this->authorizeRun($qaRun);
        $qaRun->load(['prompt', 'reportUrl', 'result']);

        return view('qa-runs.show', ['run' => $qaRun]);
    }

    public function destroy(QaRun $qaRun): RedirectResponse
    {
        $this->authorizeRun($qaRun);
        $qaRun->delete();

        return redirect()->route('qa-runs.index')->with('status', __('Run deleted.'));
    }

    public function toggle(QaRun $qaRun): RedirectResponse
    {
        $this->authorizeRun($qaRun);
        $qaRun->update(['is_active' => ! $qaRun->is_active]);

        return back()->with('status', __('Run updated.'));
    }

    public function retry(QaRun $qaRun): RedirectResponse
    {
        $this->authorizeRun($qaRun);
        $qaRun->result()?->delete();
        $qaRun->update([
            'status' => 'pending',
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);
        ProcessQA::dispatch($qaRun->id);

        return back()->with('status', __('Run re-queued.'));
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:qa_runs,id'],
            'action' => ['required', 'string', 'in:retry,delete,activate,deactivate'],
        ]);

        $runs = QaRun::whereIn('id', $data['ids'])->get();

        /** @var QaRun $run */
        foreach ($runs as $run) {
            $this->authorizeRun($run);
        }

        switch ($data['action']) {
            case 'retry':
                /** @var QaRun $run */
                foreach ($runs as $run) {
                    $run->result()?->delete();
                    $run->update([
                        'status' => 'pending',
                        'error_message' => null,
                        'started_at' => null,
                        'completed_at' => null,
                    ]);
                    ProcessQA::dispatch($run->id);
                }
                $message = __('Selected runs re-queued.');
                break;
            case 'delete':
                /** @var QaRun $run */
                foreach ($runs as $run) {
                    $run->delete();
                }
                $message = __('Selected runs deleted.');
                break;
            case 'activate':
                QaRun::whereIn('id', $data['ids'])->update(['is_active' => true]);
                $message = __('Selected runs activated.');
                break;
            case 'deactivate':
                QaRun::whereIn('id', $data['ids'])->update(['is_active' => false]);
                $message = __('Selected runs deactivated.');
                break;
        }

        return back()->with('status', $message);
    }

    private function authorizeRun(QaRun $qaRun): void
    {
        $qaRun->loadMissing('reportUrl.csvUploadBatch');
        $batch = $qaRun->reportUrl?->csvUploadBatch;
        abort_unless($batch && $batch->user_id === auth()->id(), 403);
    }
}
