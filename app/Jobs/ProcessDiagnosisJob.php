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

    private const USER_FACING_FAILURE_MESSAGE = 'Unable to complete diagnosis right now. Please try again in a moment.';

    public int $tries = 3;
    public int $timeout = 120;
    public bool $failOnTimeout = true;

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

        $staleProcessingSeconds = max(1, (int) config('queue.diagnosis_stale_processing_seconds', 180));
        $staleProcessingCutoff = Carbon::now()->subSeconds($staleProcessingSeconds);

        $updated = Diagnosis::query()
            ->where('id', $this->diagnosisId)
            ->where('status', '!=', Diagnosis::STATUS_COMPLETED)
            ->where(function ($query) use ($staleProcessingCutoff): void {
                $query->where('status', '!=', Diagnosis::STATUS_PROCESSING)
                    ->orWhereNull('attempted_at')
                    ->orWhere('attempted_at', '<=', $staleProcessingCutoff);
            })
            ->update([
                'status' => Diagnosis::STATUS_PROCESSING,
                'attempted_at' => Carbon::now(),
                'failure_reason' => null,
            ]);

        if ($updated === 0) {
            return; // Another worker is already processing or it's completed
        }

        $diagnosis->refresh();
        try {
            $diagnoseService->completeDiagnosis($diagnosis);
        } catch (\Throwable $exception) {
            report($exception);

            $diagnosis->update([
                'status' => Diagnosis::STATUS_FAILED,
                'failure_reason' => self::USER_FACING_FAILURE_MESSAGE,
            ]);

            throw $exception;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Diagnosis::query()
            ->where('id', $this->diagnosisId)
            ->whereNotIn('status', [Diagnosis::STATUS_COMPLETED, Diagnosis::STATUS_FAILED])
            ->update([
                'status' => Diagnosis::STATUS_FAILED,
                'failure_reason' => self::USER_FACING_FAILURE_MESSAGE,
            ]);
    }
}
