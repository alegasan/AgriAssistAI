<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates admin password when current password is valid for regular accounts', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'username' => 'admin-password-update',
        'password' => 'old-password-123',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.profile.password.update'), [
            'current_password' => 'old-password-123',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect();

    $admin->refresh();

    expect(Hash::check('new-password-123', $admin->password))->toBeTrue();
});

it('requires current password for regular admin accounts', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'username' => 'admin-password-requires-current',
        'password' => 'old-password-123',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.profile.show'))
        ->put(route('admin.profile.password.update'), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect(route('admin.profile.show'))
        ->assertSessionHasErrors('current_password');
});

it('allows social admin accounts to set a password without current password', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'username' => 'admin-social-password-update',
        'provider' => 'google',
        'provider_id' => 'google-123',
        'password' => 'random-social-password',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.profile.password.update'), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect();

    $admin->refresh();

    expect(Hash::check('new-password-123', $admin->password))->toBeTrue();
});

it('blocks non-admins from updating an admin password', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'user-admin-password-update-blocked',
    ]);

    $this->actingAs($user)
        ->put(route('admin.profile.password.update'), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertForbidden();
});
