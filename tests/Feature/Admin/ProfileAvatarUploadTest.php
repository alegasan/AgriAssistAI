<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows admins to upload a profile avatar', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'admin',
        'username' => 'admin-avatar-upload',
    ]);

    $image = UploadedFile::fake()->image('avatar.jpg');

    $this->actingAs($admin)
        ->post(route('admin.profile.avatar.upload'), [
            'avatar' => $image,
        ])
        ->assertRedirect();

    $admin->refresh();

    expect($admin->getRawOriginal('avatar'))->toBeString();
    expect($admin->getRawOriginal('avatar'))->toStartWith("avatars/{$admin->id}/");
    Storage::disk('public')->assertExists($admin->getRawOriginal('avatar'));
});

it('blocks non-admins from uploading an admin avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'user-avatar-upload-blocked',
    ]);

    $image = UploadedFile::fake()->image('avatar.jpg');

    $this->actingAs($user)
        ->post(route('admin.profile.avatar.upload'), [
            'avatar' => $image,
        ])
        ->assertForbidden();
});
