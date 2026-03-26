<?php

use App\Models\Diagnosis;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('diagnoses:mark-stale-failed', function () {
    $staleSeconds = max(1, (int) config('queue.diagnosis_stale_processing_seconds', 180));
    $cutoff = Carbon::now()->subSeconds($staleSeconds);

    $updated = Diagnosis::query()
        ->where('status', Diagnosis::STATUS_PROCESSING)
        ->where(function ($query) use ($cutoff): void {
            $query->whereNull('attempted_at')
                ->orWhere('attempted_at', '<=', $cutoff);
        })
        ->update([
            'status' => Diagnosis::STATUS_FAILED,
            'failure_reason' => 'Unable to complete diagnosis right now. Please try again in a moment.',
        ]);

    $this->info("Marked {$updated} stale diagnosis record(s) as failed.");
})->purpose('Mark stale processing diagnoses as failed to avoid indefinite processing states');

Schedule::command('diagnoses:mark-stale-failed')
    ->everyMinute()
    ->withoutOverlapping();
