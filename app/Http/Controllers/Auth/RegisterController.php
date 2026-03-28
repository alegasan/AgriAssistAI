<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Form\RegisterRequest;
use App\Models\User;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(RegisterRequest $request)
    {

        $request->validated();

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        auth()->login($user);

        return redirect()->route('client.dashboard');
    }
}
