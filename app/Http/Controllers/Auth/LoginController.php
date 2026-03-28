<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Form\LoginRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! auth()->attempt([$loginField => $credentials['login'], 'password' => $credentials['password']])) {
            return back()->withErrors([
                'login' => 'The provided credentials do not match our records.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();
        $user = auth()->user();

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'login' => 'The provided credentials do not match our records.',
            ])->onlyInput('login');
        }

        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('client.dashboard');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
