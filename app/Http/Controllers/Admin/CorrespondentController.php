<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCorrespondentRequest;
use App\Models\AuditLog;
use App\Models\Correspondent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CorrespondentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $program = in_array($request->string('program')->toString(), ['active', 'inactive', 'none'], true)
            ? $request->string('program')->toString()
            : null;

        $users = User::query()
            ->with('correspondent')
            ->when($search !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query->whereLike('name', "%{$search}%")->orWhereLike('email', "%{$search}%"),
            ))
            ->when($program === 'active', fn (Builder $query) => $query->whereRelation('correspondent', 'is_active', true))
            ->when($program === 'inactive', fn (Builder $query) => $query->whereRelation('correspondent', 'is_active', false))
            ->when($program === 'none', fn (Builder $query) => $query->doesntHave('correspondent'))
            ->withCount('articles')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio,
                'articles_count' => $user->articles_count,
                'correspondent' => $user->correspondent ? [
                    'id' => $user->correspondent->id,
                    'bio' => $user->correspondent->bio,
                    'is_active' => $user->correspondent->is_active,
                    'updated_at' => $user->correspondent->updated_at?->toIso8601String(),
                ] : null,
            ]);

        return Inertia::render('admin/correspondents/Index', [
            'users' => $users,
            'filters' => ['search' => $search, 'program' => $program],
        ]);
    }

    /**
     * Grant correspondent status, reactivating an earlier correspondent record when one exists.
     */
    public function store(StoreCorrespondentRequest $request): RedirectResponse
    {
        $user = $request->correspondentUser();

        DB::transaction(function () use ($request, $user): void {
            $correspondent = Correspondent::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['bio' => $request->validated('bio'), 'is_active' => true],
            );

            if ($user->role === UserRole::Member) {
                $user->update(['role' => UserRole::Correspondent]);
            }

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::CorrespondentGranted,
                subject: $correspondent,
                metadata: ['user_id' => $user->id, 'user_name' => $user->name],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is now a correspondent.', ['name' => $user->name])]);

        return back();
    }

    /**
     * Revoke correspondent status. The record is kept so the history survives.
     */
    public function destroy(Request $request, Correspondent $correspondent): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        if (! $correspondent->is_active) {
            throw ValidationException::withMessages([
                'correspondent' => 'This correspondent is already inactive.',
            ]);
        }

        $user = $correspondent->user;

        DB::transaction(function () use ($request, $correspondent, $user, $validated): void {
            $correspondent->update(['is_active' => false]);

            if ($user->role === UserRole::Correspondent) {
                $user->update(['role' => UserRole::Member]);
            }

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::CorrespondentRevoked,
                subject: $correspondent,
                reason: $validated['reason'],
                metadata: ['user_id' => $user->id, 'user_name' => $user->name],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is no longer a correspondent.', ['name' => $user->name])]);

        return back();
    }
}
