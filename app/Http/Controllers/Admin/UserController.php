<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? null;

        $users = User::query()
            ->where('role', '!=', 'admin')
            ->select(['id', 'name', 'email', 'is_active', 'created_at'])
            ->withCount('diagnoses')
            ->when($search !== '', function ($query) use ($search) {
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
                $pattern = "%{$escaped}%";

                $query->where(function ($query) use ($pattern) {
                    $query->where('name', 'like', $pattern)
                        ->orWhere('email', 'like', $pattern)
                        ->orWhere('username', 'like', $pattern);
                });
            })
            ->when($status === 'active', fn($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn($query) => $query->where('is_active', false))
            ->latest('created_at')
            ->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search !== '' ? $search : null,
                'status' => $status,
            ],
        ]);
    }

    public function toggleStatus(User $user)
    {
        $this->authorize('toggleStatus', $user);

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot change status of admin users.'], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => 'User status updated successfully.',
            'is_active' => $user->is_active,
        ]);
    }
}
