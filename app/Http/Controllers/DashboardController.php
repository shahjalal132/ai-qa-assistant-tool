<?php

namespace App\Http\Controllers;

use App\Models\CsvUploadBatch;
use App\Models\Prompt;
use App\Models\QaRun;
use App\Models\Result;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'batches_count' => CsvUploadBatch::count(),
            'prompts_count' => Prompt::count(),
            'qa_runs_count' => QaRun::count(),
            'results_count' => Result::count(),
            'latest_runs' => QaRun::with(['reportUrl', 'prompt'])->latest()->take(5)->get(),
        ];

        return view('dashboard', $stats);
    }
}
