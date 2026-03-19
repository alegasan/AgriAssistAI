<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Form\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(RegisterRequest $request)
    {

            $key = 'register:' . $request->input('email');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429);
        }

        RateLimiter::hit($key);
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