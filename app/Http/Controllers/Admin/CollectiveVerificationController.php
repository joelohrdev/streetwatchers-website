<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Collective;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CollectiveVerificationController extends Controller
{
    public function store(Request $request, Collective $collective): RedirectResponse
    {
        $this->setVerified($request->user(), $collective, true);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is now verified.', ['name' => $collective->name])]);

        return back();
    }

    public function destroy(Request $request, Collective $collective): RedirectResponse
    {
        $this->setVerified($request->user(), $collective, false);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is no longer verified.', ['name' => $collective->name])]);

        return back();
    }

    private function setVerified(User $actor, Collective $collective, bool $isVerified): void
    {
        if ($collective->is_verified === $isVerified) {
            return;
        }

        DB::transaction(function () use ($actor, $collective, $isVerified): void {
            $collective->update(['is_verified' => $isVerified]);

            AuditLog::record(
                actor: $actor,
                action: $isVerified ? AuditAction::CollectiveVerified : AuditAction::CollectiveUnverified,
                subject: $collective,
            );
        });
    }
}
