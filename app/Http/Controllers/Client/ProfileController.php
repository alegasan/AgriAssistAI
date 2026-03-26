<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileAvatarUploadRequest;
use App\Http\Requests\ProfilePasswordUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $avatar = $user->getRawOriginal('avatar');
        $avatarUrl = null;

        if (is_string($avatar) && $avatar !== '') {
            if (filter_var($avatar, FILTER_VALIDATE_URL) !== false) {
                $avatarUrl = $avatar;
            } else {
                $avatarUrl = route('client.profile.avatar.show', $user);
            }
        }

        return Inertia::render('Client/ProfileTab/Show', [
            'user' => [
                ...$user->toArray(),
                'avatar_url' => $avatarUrl,
            ],
        ]);
    }

    public function avatar(Request $request, User $user): StreamedResponse|RedirectResponse|Response
    {
        abort_unless($request->user()?->is($user), 403);

        $avatar = $user->getRawOriginal('avatar');

        if (! is_string($avatar) || $avatar === '') {
            abort(404);
        }

        if (filter_var($avatar, FILTER_VALIDATE_URL) !== false) {
            return redirect()->away($avatar);
        }

        $disk = 'public';
        $storage = Storage::disk($disk);

        abort_unless($storage->exists($avatar), 404);

        $filename = basename($avatar);
        $mimeType = $storage->mimeType($avatar) ?: 'application/octet-stream';

        return $storage->response($avatar, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function uploadAvatar(ProfileAvatarUploadRequest $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->is($user), 403);

        $disk = 'public';
        $oldAvatar = $user->getRawOriginal('avatar');

        $path = $request->file('avatar')->store("avatars/{$user->id}", $disk);

        if (! is_string($path) || $path === '') {
            Log::error('Avatar upload failed: store() did not return a valid path.', [
                'user_id' => $user->id,
                'disk' => $disk,
            ]);

            return back()->with('error', 'Unable to upload profile photo right now. Please try again.');
        }

        if (is_string($oldAvatar) && filter_var($oldAvatar, FILTER_VALIDATE_URL) === false) {
            Storage::disk($disk)->delete($oldAvatar);
        }

        $user->update([
            'avatar' => $path,
        ]);

        return back()->with('success', 'Profile photo updated successfully.');
    }

    public function updatePassword(ProfilePasswordUpdateRequest $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->is($user), 403);

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
