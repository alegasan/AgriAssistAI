<?php

namespace App\Console\Commands;

use App\Models\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ActivitiesPiiRetentionCommand extends Command
{
    protected $signature = 'activities:apply-pii-retention {--dry-run : Show how many records would be affected without changing data}';

    protected $description = 'Delete or anonymize Activity.ip_address and Activity.user_agent after the configured retention period';

    public function handle(): int
    {
        $days = (int) config('activity.pii_retention_days', 30);
        $days = max(0, $days);

        $action = (string) config('activity.pii_retention_action', 'anonymize');
        if (! in_array($action, ['anonymize', 'delete'], true)) {
            $this->error("Invalid config('activity.pii_retention_action'): {$action}. Expected 'anonymize' or 'delete'.");
            return self::FAILURE;
        }

        if ($days === 0) {
            $this->info('PII retention is disabled (0 days).');
            return self::SUCCESS;
        }

        $cutoff = Carbon::now()->subDays($days);

        $query = Activity::query()
            ->where('occurred_at', '<', $cutoff)
            ->where(function ($q): void {
                $q->whereNotNull('ip_address')
                    ->orWhereNotNull('user_agent');
            });

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("[dry-run] {$count} activity record(s) would be affected ({$action}) before {$cutoff->toDateTimeString()}.");
            return self::SUCCESS;
        }

        if ($count === 0) {
            $this->info('No activity records require PII retention processing.');
            return self::SUCCESS;
        }

        if ($action === 'delete') {
            $deleted = $query->delete();
            $this->info("Deleted {$deleted} activity record(s) older than {$days} day(s).");
            return self::SUCCESS;
        }

        $updated = $query->update([
            'ip_address' => null,
            'user_agent' => null,
            'updated_at' => now(),
        ]);

        $this->info("Anonymized {$updated} activity record(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
