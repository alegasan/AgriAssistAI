<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Models\Activity;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(
        ActivityAction $action,
        array $properties = [],
        ?Model $subject = null,
        ?User $causer = null,
        ?CarbonInterface $occurredAt = null,
        ?Request $request = null,
    ): Activity {
        $activity = new Activity();

        $activity->action = $action->value;
        $activity->occurred_at = $occurredAt?->toDateTimeString() ?? now();
        $activity->properties = $properties !== [] ? $properties : null;

        if ($causer) {
            $activity->causer()->associate($causer);
        }

        if ($subject) {
            $activity->subject()->associate($subject);
        }

        if ($request) {
            $activity->ip_address = $request->ip();
            $activity->user_agent = $request->userAgent();
        }

        $activity->save();

        return $activity;
    }
}
