<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\CritiqueGroupStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CritiqueGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CritiqueGroupDissolutionController extends Controller
{
    /**
     * Close a critique group early.
     */
    public function store(Request $request, CritiqueGroup $critiqueGroup): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($critiqueGroup->status === CritiqueGroupStatus::Closed) {
            throw ValidationException::withMessages([
                'critique_group' => 'This group is already closed.',
            ]);
        }

        DB::transaction(function () use ($request, $critiqueGroup, $validated): void {
            $oldStatus = $critiqueGroup->status;
            $critiqueGroup->update(['status' => CritiqueGroupStatus::Closed]);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::CritiqueGroupDissolved,
                subject: $critiqueGroup,
                oldStatus: $oldStatus,
                newStatus: CritiqueGroupStatus::Closed,
                reason: $validated['reason'] ?? null,
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Critique group dissolved.')]);

        return back();
    }
}
