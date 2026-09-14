<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserStatusController extends Controller
{
    /**
     * Suspend, ban, or reinstate a user. Blocked users are signed out on their next request.
     */
    public function update(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $newStatus = UserStatus::from($request->validated('status'));
        $oldStatus = $user->status;

        if ($newStatus === $oldStatus) {
            return back();
        }

        DB::transaction(function () use ($request, $user, $oldStatus, $newStatus): void {
            $user->forceFill(['status' => $newStatus])->save();

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::UserStatusUpdated,
                subject: $user,
                oldStatus: $oldStatus,
                newStatus: $newStatus,
                reason: $request->validated('reason'),
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is now :status.', [
            'name' => $user->name,
            'status' => Str::lower($newStatus->label()),
        ])]);

        return back();
    }
}
