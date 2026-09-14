<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveCritiqueGroupMemberRequest;
use App\Models\AuditLog;
use App\Models\CritiqueGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CritiqueGroupMemberController extends Controller
{
    /**
     * Move a member into a different critique group. Their past submissions stay with the original group.
     */
    public function update(MoveCritiqueGroupMemberRequest $request, CritiqueGroup $critiqueGroup, User $user): RedirectResponse
    {
        $this->ensureMember($critiqueGroup, $user);

        $target = $request->targetGroup();

        DB::transaction(function () use ($request, $critiqueGroup, $user, $target): void {
            $critiqueGroup->members()->detach($user);
            $target->members()->attach($user);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::CritiqueGroupMemberMoved,
                subject: $target,
                metadata: [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'from_group_id' => $critiqueGroup->id,
                    'to_group_id' => $target->id,
                ],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name moved to another group.', ['name' => $user->name])]);

        return back();
    }

    public function destroy(Request $request, CritiqueGroup $critiqueGroup, User $user): RedirectResponse
    {
        $this->ensureMember($critiqueGroup, $user);

        DB::transaction(function () use ($request, $critiqueGroup, $user): void {
            $critiqueGroup->members()->detach($user);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::CritiqueGroupMemberRemoved,
                subject: $critiqueGroup,
                metadata: ['user_id' => $user->id, 'user_name' => $user->name],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name removed from the group.', ['name' => $user->name])]);

        return back();
    }

    private function ensureMember(CritiqueGroup $critiqueGroup, User $user): void
    {
        abort_unless($critiqueGroup->members()->whereKey($user->id)->exists(), 404);
    }
}
