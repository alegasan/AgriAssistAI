<?php

namespace App\Policies;

use App\Models\User;
use App\Models\user;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    // public function viewOwnProfile(User $authUser, User $user): Response
    // {
    //     return $authUser->id === $user->id
    //         ? Response::allow()
    //         : Response::deny('You can only view your own profile.');
    // }
}
