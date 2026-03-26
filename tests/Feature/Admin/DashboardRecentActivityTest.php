<?php

use App\Enums\ActivityAction;
use App\Models\Activity;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows recentActivity on the admin dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'username' => 'admin-dashboard-activity',
    ]);

    $farmer = User::factory()->create([
        'role' => 'user',
        'username' => 'farmer-dashboard-activity',
        'name' => 'Suresh Patel',
    ]);

    Activity::query()->create([
        'action' => ActivityAction::FarmerRegistered->value,
        'occurred_at' => now()->subMinutes(5),
        'subject_type' => $farmer::class,
        'subject_id' => $farmer->id,
        'properties' => [
            'name' => $farmer->name,
            'role' => $farmer->role,
            'user_id' => $farmer->id,
        ],
    ]);

    Activity::query()->create([
        'action' => ActivityAction::AdminUserStatusToggled->value,
        'occurred_at' => now()->subMinute(),
        'causer_id' => $admin->id,
        'subject_type' => $farmer::class,
        'subject_id' => $farmer->id,
        'properties' => [
            'target_user_id' => $farmer->id,
            'target_user_name' => $farmer->name,
            'is_active' => false,
        ],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('recentActivity', 2)
            ->where('recentActivity.0.icon_key', 'user-cog')
            ->where('recentActivity.0.message', "User status changed: {$farmer->name} is now inactive")
            ->where('recentActivity.1.icon_key', 'users')
            ->where('recentActivity.1.message', "New farmer registered: {$farmer->name}"));
});
