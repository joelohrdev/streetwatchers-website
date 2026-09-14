<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->enum('status', UserStatus::class);
        $role = $request->enum('role', UserRole::class);
        $search = $request->string('search')->trim()->toString();

        $users = User::query()
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($role, fn (Builder $query) => $query->where('role', $role))
            ->when($search !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query->whereLike('name', "%{$search}%")->orWhereLike('email', "%{$search}%"),
            ))
            ->withCount('photos')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'status' => $user->status->value,
                'photos_count' => $user->photos_count,
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => ['status' => $status?->value, 'role' => $role?->value, 'search' => $search],
            'statuses' => UserStatus::options(),
            'roles' => UserRole::options(),
        ]);
    }
}
