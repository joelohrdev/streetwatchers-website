<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\ChapterMemberRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreChapterAdminRequest;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ChapterAdminController extends Controller
{
    /**
     * Make a user an admin of the chapter, adding them as a member first if needed.
     */
    public function store(StoreChapterAdminRequest $request, Chapter $chapter): RedirectResponse
    {
        $user = $request->userToPromote();
        $membership = $chapter->members()->whereKey($user->id)->first()?->pivot;

        if ($membership?->role === ChapterMemberRole::Admin) {
            throw ValidationException::withMessages([
                'email' => __(':name is already an admin of this chapter.', ['name' => $user->name]),
            ]);
        }

        DB::transaction(function () use ($request, $chapter, $user, $membership): void {
            if ($membership === null) {
                $chapter->members()->attach($user, ['role' => ChapterMemberRole::Admin]);
            } else {
                $chapter->members()->updateExistingPivot($user->id, ['role' => ChapterMemberRole::Admin]);
            }

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::ChapterAdminAdded,
                subject: $chapter,
                oldStatus: $membership?->role,
                newStatus: ChapterMemberRole::Admin,
                metadata: ['user_id' => $user->id, 'user_name' => $user->name],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is now a chapter admin.', ['name' => $user->name])]);

        return back();
    }

    /**
     * Remove a user's admin role. They stay in the chapter as a regular member.
     */
    public function destroy(Request $request, Chapter $chapter, User $user): RedirectResponse
    {
        $isAdmin = $chapter->members()
            ->whereKey($user->id)
            ->wherePivot('role', ChapterMemberRole::Admin)
            ->exists();

        abort_unless($isAdmin, 404);

        DB::transaction(function () use ($request, $chapter, $user): void {
            $chapter->members()->updateExistingPivot($user->id, ['role' => ChapterMemberRole::Member]);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::ChapterAdminRemoved,
                subject: $chapter,
                oldStatus: ChapterMemberRole::Admin,
                newStatus: ChapterMemberRole::Member,
                metadata: ['user_id' => $user->id, 'user_name' => $user->name],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is no longer a chapter admin.', ['name' => $user->name])]);

        return back();
    }
}
