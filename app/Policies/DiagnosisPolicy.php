<?php

namespace App\Policies;

use App\Models\Diagnosis;
use App\Models\User;

class DiagnosisPolicy
{
    public function delete(User $user, Diagnosis $diagnosis): bool
    {
        return $user->id === $diagnosis->user_id;
    }
}
