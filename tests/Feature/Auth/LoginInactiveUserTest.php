<?php

use App\Models\User;

it('blocks login for deactivated users', function () {
    $user = User::factory()->create([
        'username' => 'inactive-user',
    ]);

    $user->forceFill([
        'is_active' => false,
    ])->save();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'login' => 'inactive-user',
            'password' => 'password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'login' => 'The provided credentials do not match our records.',
        ]);

    $this->assertGuest();
});

it('allows login for active users', function () {
    User::factory()->create([
        'username' => 'active-user',
        'password' => 'password',
        'is_active' => true,
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'login' => 'active-user',
            'password' => 'password',
        ])
        ->assertRedirect(route('client.dashboard'));

    $this->assertAuthenticated();
});
