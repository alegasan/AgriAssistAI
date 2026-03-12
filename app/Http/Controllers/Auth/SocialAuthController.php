<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors([
                'login' => 'Unable to authenticate with Google. Please try again.',
            ]);
        }

        $email = $googleUser->getEmail();

        if (!$email) {
            return redirect()->route('login')->withErrors([
                'login' => 'Google did not provide an email address.',
            ]);
        }

        $provider = 'google';

        $existingProviderUser = User::query()
            ->where('provider', $provider)
            ->where('provider_id', $googleUser->getId())
            ->first();

        if ($existingProviderUser) {
            Auth::login($existingProviderUser);
            $request->session()->regenerate();

            return $this->redirectByRole($existingProviderUser);
        }

        $existingEmailUser = User::query()->where('email', $email)->first();

        if ($existingEmailUser) {
            return redirect()->route('login')->withErrors([
                'login' => 'This email is already registered. Please log in with your password to link Google.',
            ]);
        }

        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
            'email' => $email,
            'username' => $this->makeUniqueUsername($email),
            'password' => Str::password(32),
            'provider' => $provider,
            'provider_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        if (($googleUser->user['email_verified'] ?? false) === true) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    private function redirectByRole(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('client.dashboard');
    }

    private function makeUniqueUsername(string $email): string
    {
        $base = Str::before($email, '@');
        $slug = Str::slug($base, '_');
        $username = $slug !== '' ? $slug : 'user';

        $suffix = 1;
        $candidate = $username;

        while (User::query()->where('username', $candidate)->exists()) {
            $candidate = $username.'_'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
