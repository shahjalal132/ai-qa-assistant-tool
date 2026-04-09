<?php

namespace App\Http\Controllers;

use App\Models\CsvUploadBatch;
use App\Models\ReportUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CsvUploadBatchController extends Controller
{
    public function index(): View
    {
        $batches = CsvUploadBatch::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('csv-upload-batches.index', compact('batches'));
    }

    public function create(): View
    {
        return view('csv-upload-batches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $file = $request->file('csv');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['csv' => __('Could not read the CSV file.')])->withInput();
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false || $headerRow === []) {
            fclose($handle);

            return back()->withErrors(['csv' => __('The CSV file is empty.')])->withInput();
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
        $enIdx = $this->resolveColumn($headers, ['english_url', 'english']);
        $cyIdx = $this->resolveColumn($headers, ['welsh_url', 'welsh', 'cymraeg']);

        if ($enIdx === null || $cyIdx === null) {
            fclose($handle);

            return back()->withErrors([
                'csv' => __('CSV must include english_url (or english) and welsh_url (or welsh) columns.'),
            ])->withInput();
        }

        $batch = CsvUploadBatch::query()->create([
            'filename' => $file->getClientOriginalName(),
            'user_id' => auth()->id(),
            'total_rows' => 0,
        ]);

        $count = 0;
        $buffer = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $english = trim((string) ($row[$enIdx] ?? ''));
            $welsh = trim((string) ($row[$cyIdx] ?? ''));
            if ($english === '' || $welsh === '') {
                continue;
            }

            $metadata = [];
            foreach ($headers as $i => $key) {
                if ($i === $enIdx || $i === $cyIdx) {
                    continue;
                }
                if (! isset($row[$i])) {
                    continue;
                }
                $metadata[$key] = $row[$i];
            }

            $buffer[] = [
                'csv_upload_batch_id' => $batch->id,
                'english_url' => $english,
                'welsh_url' => $welsh,
                'metadata' => $metadata === [] ? null : $metadata,
                'status' => 'pending',
            ];
            $count++;

            if (count($buffer) >= 500) {
                ReportUrl::insertImportChunks($buffer);
                $buffer = [];
            }
        }

        ReportUrl::insertImportChunks($buffer);

        fclose($handle);

        $batch->update(['total_rows' => $count]);

        return redirect()->route('csv-upload-batches.show', $batch)
            ->with('status', __('Imported :count URL pair(s).', ['count' => $count]));
    }

    public function apiItems(Request $request, CsvUploadBatch $batch): JsonResponse
    {
        $this->authorizeOwner($batch);

        $reportUrls = ReportUrl::query()
            ->where('csv_upload_batch_id', $batch->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sq) use ($search) {
                    $sq->where('english_url', 'like', "%{$search}%")
                       ->orWhere('welsh_url', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate(50);

        return response()->json($reportUrls);
    }

    public function show(Request $request, CsvUploadBatch $csvUploadBatch): View
    {
        $this->authorizeOwner($csvUploadBatch);

        $reportUrls = ReportUrl::query()
            ->where('csv_upload_batch_id', $csvUploadBatch->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sq) use ($search) {
                    $sq->where('english_url', 'like', "%{$search}%")
                       ->orWhere('welsh_url', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('csv-upload-batches.show', [
            'batch' => $csvUploadBatch,
            'reportUrls' => $reportUrls
        ]);
    }

    public function destroyUrl(CsvUploadBatch $batch, ReportUrl $url): RedirectResponse
    {
        $this->authorizeOwner($batch);
        abort_unless($url->csv_upload_batch_id === $batch->id, 403);

        $url->delete();

        return back()->with('status', __('URL pair deleted.'));
    }

    public function bulkActionUrls(Request $request, CsvUploadBatch $batch): RedirectResponse
    {
        $this->authorizeOwner($batch);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:report_urls,id'],
            'action' => ['required', 'string', 'in:delete'],
        ]);

        ReportUrl::query()
            ->where('csv_upload_batch_id', $batch->id)
            ->whereIn('id', $data['ids'])
            ->delete();

        return back()->with('status', __('Selected URL pairs deleted.'));
    }

    public function destroy(CsvUploadBatch $csvUploadBatch): RedirectResponse
    {
        $this->authorizeOwner($csvUploadBatch);
        $csvUploadBatch->delete();

        return redirect()->route('csv-upload-batches.index')
            ->with('status', __('Batch deleted.'));
    }

    private function authorizeOwner(CsvUploadBatch $batch): void
    {
        abort_unless($batch->user_id === auth()->id(), 403);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string>  $candidates
     */
    private function resolveColumn(array $headers, array $candidates): ?int
    {
        foreach ($candidates as $name) {
            $idx = array_search($name, $headers, true);
            if ($idx !== false) {
                return (int) $idx;
            }
        }

        return null;
    }

    /**
     * @param  list<string|null>|false  $row
     */
    private function rowIsEmpty(array|false $row): bool
    {
        if ($row === false) {
            return true;
        }
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
