<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates password when current password is valid for regular accounts', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'password' => 'old-password-123',
    ]);

    $this->actingAs($user)
        ->put(route('client.profile.password.update', $user), [
            'current_password' => 'old-password-123',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect();

    $user->refresh();

    expect(Hash::check('new-password-123', $user->password))->toBeTrue();
});

it('requires current password for regular accounts', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'password' => 'old-password-123',
    ]);

    $this->actingAs($user)
        ->from(route('client.profile', $user))
        ->put(route('client.profile.password.update', $user), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect(route('client.profile', $user))
        ->assertSessionHasErrors('current_password');
});

it('allows social accounts to set a password without current password', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'provider' => 'google',
        'provider_id' => 'google-123',
        'password' => 'random-social-password',
    ]);

    $this->actingAs($user)
        ->put(route('client.profile.password.update', $user), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect();

    $user->refresh();

    expect(Hash::check('new-password-123', $user->password))->toBeTrue();
});
