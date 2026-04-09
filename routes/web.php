<?php

use App\Http\Controllers\CsvUploadBatchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\QaRunController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('csv-upload-batches/{batch}/items', [CsvUploadBatchController::class, 'apiItems'])->name('csv-upload-batches.items');
    Route::delete('csv-upload-batches/{batch}/urls/{url}', [CsvUploadBatchController::class, 'destroyUrl'])->name('csv-upload-batches.destroy-url');
    Route::post('csv-upload-batches/{batch}/bulk-action-urls', [CsvUploadBatchController::class, 'bulkActionUrls'])->name('csv-upload-batches.bulk-action-urls');
    Route::resource('csv-upload-batches', CsvUploadBatchController::class)->except(['edit', 'update']);

    Route::resource('prompts', PromptController::class);

    Route::post('qa-runs-bulk', [QaRunController::class, 'bulkAction'])->name('qa-runs.bulk-action');
    Route::post('qa-runs/{qa_run}/toggle', [QaRunController::class, 'toggle'])->name('qa-runs.toggle');
    Route::post('qa-runs/{qa_run}/retry', [QaRunController::class, 'retry'])->name('qa-runs.retry');
    Route::resource('qa-runs', QaRunController::class)->except(['edit', 'update']);

    Route::post('results-bulk-delete', [ResultController::class, 'bulkDestroy'])->name('results.bulk-destroy');
    Route::post('results-bulk-export', [ResultController::class, 'bulkExport'])->name('results.bulk-export');
    Route::get('results-export', [ResultController::class, 'export'])->name('results.export');
    Route::get('results/{result}/download', [ResultController::class, 'download'])->name('results.download');
    Route::resource('results', ResultController::class)->only(['index', 'show', 'destroy']);

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
