<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        return Inertia::render('Client/ProfileTab/Show', [
            'user' => $user
        ]);
    }
}
