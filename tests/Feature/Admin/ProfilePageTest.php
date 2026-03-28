<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the admin profile page for admins', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'username' => 'admin-profile',
        'name' => 'Admin Person',
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.profile.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Profile')
            ->where('user.id', $admin->id)
            ->where('user.name', $admin->name)
            ->where('user.email', $admin->email));
});

it('blocks non-admins from the admin profile page', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'not-admin-profile',
    ]);

    $this->actingAs($user)
        ->get(route('admin.profile.show'))
        ->assertForbidden();
});
