<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

      
        $key = 'forgot-password:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => [Lang::get('Too many attempts. Please try again later.')],
            ]);
        }
        RateLimiter::hit($key, 60);

        $status = Password::sendResetLink(
            $request->only('email')
        );


        if ($status === Password::RESET_LINK_SENT) {
            return redirect()->route('password.request')->with('status', 'A password reset link has been sent to your email address.');
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
