<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Enums\ChapterMemberRole;
use App\Models\AuditLog;
use App\Models\Chapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Organizers making a member an organizer, or stepping down themselves. Site admins have their own controls.
 */
class ChapterOrganizerController extends Controller
{
    public function store(Request $request, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('organize', $chapter);

        $validated = $request->validate(['user_id' => ['required', 'integer']]);

        $member = $chapter->members()
            ->whereKey($validated['user_id'])
            ->wherePivot('role', ChapterMemberRole::Member)
            ->first();

        if ($member === null) {
            throw ValidationException::withMessages(['user_id' => 'Only members of the group can become organizers.']);
        }

        DB::transaction(function () use ($request, $chapter, $member): void {
            $chapter->members()->updateExistingPivot($member->id, ['role' => ChapterMemberRole::Admin]);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::ChapterAdminAdded,
                subject: $chapter,
                oldStatus: ChapterMemberRole::Member,
                newStatus: ChapterMemberRole::Admin,
                metadata: ['user_id' => $member->id, 'user_name' => $member->name],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$member->name} is now an organizer."]);

        return back();
    }

    /**
     * Step down as an organizer and stay on as a member. A group always keeps at least one organizer.
     */
    public function destroy(Request $request, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('organize', $chapter);

        $organizerCount = $chapter->memberships()->where('role', ChapterMemberRole::Admin)->count();

        if ($organizerCount < 2) {
            throw ValidationException::withMessages([
                'organizer' => 'Make someone else an organizer before you step down, so the group isn’t left without one.',
            ]);
        }

        $user = $request->user();

        DB::transaction(function () use ($chapter, $user): void {
            $chapter->members()->updateExistingPivot($user->id, ['role' => ChapterMemberRole::Member]);

            AuditLog::record(
                actor: $user,
                action: AuditAction::ChapterAdminRemoved,
                subject: $chapter,
                oldStatus: ChapterMemberRole::Admin,
                newStatus: ChapterMemberRole::Member,
                metadata: ['user_id' => $user->id, 'user_name' => $user->name],
            );
        });

        return to_route('chapters.show', $chapter)->with('status', 'organizer-stepped-down');
    }
}
