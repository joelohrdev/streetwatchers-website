<?php

namespace App\Http\Controllers;

use App\Enums\CollectiveApplicationStatus;
use App\Enums\CollectiveMemberRole;
use App\Models\CollectiveApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * A founder accepts or declines an application. It is a yes or no on joining, with no comments.
 */
class CollectiveApplicationDecisionController extends Controller
{
    public function update(Request $request, CollectiveApplication $collectiveApplication): RedirectResponse
    {
        Gate::authorize('decide', $collectiveApplication);

        $validated = $request->validate([
            'decision' => ['required', Rule::in([CollectiveApplicationStatus::Accepted->value, CollectiveApplicationStatus::Declined->value])],
        ]);

        if ($collectiveApplication->status !== CollectiveApplicationStatus::Pending) {
            throw ValidationException::withMessages([
                'decision' => 'This application has already been decided.',
            ]);
        }

        $decision = CollectiveApplicationStatus::from($validated['decision']);
        $collective = $collectiveApplication->collective;
        $applicant = $collectiveApplication->user;

        DB::transaction(function () use ($request, $collectiveApplication, $decision, $collective, $applicant): void {
            $collectiveApplication->update([
                'status' => $decision,
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
            ]);

            if ($decision === CollectiveApplicationStatus::Accepted && $collective->roleOf($applicant) === null) {
                $collective->members()->attach($applicant, ['role' => CollectiveMemberRole::Member]);
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $decision === CollectiveApplicationStatus::Accepted
                ? __(':name is now a member of :collective.', ['name' => $applicant->name, 'collective' => $collective->name])
                : __('Application from :name declined.', ['name' => $applicant->name]),
        ]);

        return back();
    }
}
