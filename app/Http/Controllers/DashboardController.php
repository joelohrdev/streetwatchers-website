<?php

namespace App\Http\Controllers;

use App\Enums\ChapterMemberRole;
use App\Enums\CollectiveApplicationStatus;
use App\Enums\CollectiveMemberRole;
use App\Models\ChapterUser;
use App\Models\CollectiveApplication;
use App\Models\CollectiveUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "Your memberships": a member's groups, collectives and applications.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $collectiveMemberships = $user->collectiveMemberships()->with('collective')->whereHas('collective')->get();

        $pendingByCollective = CollectiveApplication::query()
            ->where('status', CollectiveApplicationStatus::Pending)
            ->whereIn('collective_id', $collectiveMemberships
                ->where('role', CollectiveMemberRole::Founder)
                ->pluck('collective_id'))
            ->selectRaw('collective_id, count(*) as aggregate')
            ->groupBy('collective_id')
            ->pluck('aggregate', 'collective_id');

        return Inertia::render('Dashboard', [
            'groups' => $user->chapterMemberships()
                ->with('chapter')
                ->get()
                ->sortBy(fn (ChapterUser $membership): string => $membership->chapter->name)
                ->values()
                ->map(fn (ChapterUser $membership): array => [
                    'name' => $membership->chapter->name,
                    'slug' => $membership->chapter->slug,
                    'city' => $membership->chapter->city,
                    'country' => $membership->chapter->country->label(),
                    'status' => $membership->chapter->status->value,
                    'role' => $membership->role === ChapterMemberRole::Admin ? 'organizer' : 'member',
                ]),
            'collectives' => $collectiveMemberships
                ->sortBy(fn (CollectiveUser $membership): string => $membership->collective->name)
                ->values()
                ->map(fn (CollectiveUser $membership): array => [
                    'name' => $membership->collective->name,
                    'slug' => $membership->collective->slug,
                    'logo_url' => $membership->collective->logoUrl(),
                    'role' => $membership->role === CollectiveMemberRole::Founder ? 'founder' : 'member',
                    'pending_applications_count' => $membership->role === CollectiveMemberRole::Founder
                        ? (int) ($pendingByCollective[$membership->collective_id] ?? 0)
                        : null,
                ]),
            'applications' => $user->collectiveApplications()
                ->with('collective')
                ->whereHas('collective')
                ->latest('id')
                ->limit(10)
                ->get()
                ->map(fn (CollectiveApplication $application): array => [
                    'id' => $application->id,
                    'collective' => ['name' => $application->collective->name, 'slug' => $application->collective->slug],
                    'status' => $application->status->value,
                    'created_at' => $application->created_at?->toIso8601String(),
                ]),
        ]);
    }
}
