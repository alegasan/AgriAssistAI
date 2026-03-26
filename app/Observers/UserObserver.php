<?php

namespace App\Observers;

use App\Enums\ActivityAction;
use App\Models\User;
use App\Services\ActivityLogger;

class UserObserver
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function created(User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if (app()->runningInConsole()) {
            return;
        }

        $this->activityLogger->log(
            action: ActivityAction::FarmerRegistered,
            properties: [
                'name' => $user->name,
                'role' => $user->role,
                'user_id' => $user->id,
            ],
            subject: $user,
        );
    }
}
