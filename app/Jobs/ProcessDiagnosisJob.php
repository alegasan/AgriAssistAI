<?php

namespace App\Jobs;

use App\Models\Diagnosis;
use App\Services\DiagnoseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class ProcessDiagnosisJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(public readonly int $diagnosisId)
    {
    }

    public function handle(DiagnoseService $diagnoseService): void
    {
        $diagnosis = Diagnosis::query()->find($this->diagnosisId);

        if (! $diagnosis) {
            return;
        }

        if ($diagnosis->status === Diagnosis::STATUS_COMPLETED) {
            return;
        }

        $diagnosis->update([
            'status' => Diagnosis::STATUS_PROCESSING,
            'attempted_at' => Carbon::now(),
            'failure_reason' => null,
        ]);

        try {
            $diagnoseService->completeDiagnosis($diagnosis);
        } catch (\Throwable $exception) {
            $diagnosis->update([
                'status' => Diagnosis::STATUS_FAILED,
                'failure_reason' => mb_substr($exception->getMessage(), 0, 1000),
            ]);

            throw $exception;
        }
    }
}
