<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CritiqueGroupStatus;
use App\Http\Controllers\Controller;
use App\Models\CritiqueGroup;
use App\Models\CritiqueGroupUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CritiqueGroupController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->enum('status', CritiqueGroupStatus::class);

        $groups = CritiqueGroup::query()
            ->with('memberships.user')
            ->withCount('submissions')
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->orderByDesc('week_start')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (CritiqueGroup $group): array => [
                ...$this->summarize($group),
                'submissions_count' => $group->submissions_count,
                'members' => $group->memberships
                    ->sortBy(fn (CritiqueGroupUser $membership): string => $membership->user->name)
                    ->values()
                    ->map(fn (CritiqueGroupUser $membership): array => [
                        'id' => $membership->user->id,
                        'name' => $membership->user->name,
                        'email' => $membership->user->email,
                        'joined_at' => $membership->joined_at->toIso8601String(),
                    ]),
            ]);

        $openGroups = CritiqueGroup::query()
            ->where('status', '!=', CritiqueGroupStatus::Closed)
            ->withCount('members')
            ->orderByDesc('week_start')
            ->orderByDesc('id')
            ->get()
            ->map(fn (CritiqueGroup $group): array => [
                ...$this->summarize($group),
                'members_count' => $group->members_count,
            ]);

        return Inertia::render('admin/critique-groups/Index', [
            'groups' => $groups,
            'openGroups' => $openGroups,
            'filters' => ['status' => $status?->value],
            'statuses' => CritiqueGroupStatus::options(),
        ]);
    }

    /**
     * @return array{id: int, name: string, week_start: string, week_end: string, max_members: int, status: string}
     */
    private function summarize(CritiqueGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name ?? 'Week of '.$group->week_start->toFormattedDateString(),
            'week_start' => $group->week_start->toDateString(),
            'week_end' => $group->week_end->toDateString(),
            'max_members' => $group->max_members,
            'status' => $group->status->value,
        ];
    }
}
