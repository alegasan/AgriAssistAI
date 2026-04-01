<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use App\Models\User;

test('user can request a password reset link', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHasNoErrors();
    Notification::assertSentTo($user, ResetPassword::class);
});

test('user can reset password with valid token', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'resetme@example.com']);

    $this->post('/forgot-password', [
        'email' => 'resetme@example.com',
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $token = $notification->token;
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertCredentials([
            'email' => $user->email,
            'password' => 'new-password',
        ]);
        return true;
    });
});

test('cannot reset password with invalid token', function () {
    $user = User::factory()->create(['email' => 'failreset@example.com']);
    $response = $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $response->assertSessionHasErrors('email');
});

test('cannot reset password with an expired token', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'expired@example.com']);

    $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        DB::table(config('auth.passwords.users.table'))->where('email', $user->email)->update([
            'created_at' => now()->subMinutes(config('auth.passwords.users.expire') + 1),
        ]);

        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertSessionHasErrors('email');
        return true;
    });
});
