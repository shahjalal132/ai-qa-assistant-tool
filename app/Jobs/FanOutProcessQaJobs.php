<?php

namespace App\Jobs;

use App\Models\CsvUploadBatch;
use App\Models\QaRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FanOutProcessQaJobs implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $promptId,
        public int $csvUploadBatchId,
        public int $userId,
    ) {}

    public function handle(): void
    {
        $batch = CsvUploadBatch::query()->find($this->csvUploadBatchId);
        if (! $batch || $batch->user_id !== $this->userId) {
            return;
        }

        $ids = QaRun::query()
            ->where('prompt_id', $this->promptId)
            ->where('is_active', true)
            ->where('status', 'pending')
            ->whereHas('reportUrl', fn ($q) => $q->where('csv_upload_batch_id', $this->csvUploadBatchId))
            ->pluck('id');

        foreach ($ids as $id) {
            ProcessQA::dispatch($id);
        }
    }
}
