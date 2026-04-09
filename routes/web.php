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

    Route::resource('csv-upload-batches', CsvUploadBatchController::class)->except(['edit', 'update']);
    Route::resource('prompts', PromptController::class);
    Route::resource('qa-runs', QaRunController::class)->except(['edit', 'update']);
    Route::post('qa-runs/{qa_run}/toggle', [QaRunController::class, 'toggle'])->name('qa-runs.toggle');
    Route::post('qa-runs/{qa_run}/retry', [QaRunController::class, 'retry'])->name('qa-runs.retry');

    Route::get('results', [ResultController::class, 'index'])->name('results.index');
    Route::get('results/export', [ResultController::class, 'export'])->name('results.export');
    Route::get('results/{result}', [ResultController::class, 'show'])->name('results.show');
    Route::get('results/{result}/download', [ResultController::class, 'download'])->name('results.download');
    Route::delete('results/{result}', [ResultController::class, 'destroy'])->name('results.destroy');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
