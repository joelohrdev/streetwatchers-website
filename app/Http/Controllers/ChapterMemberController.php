<?php

namespace App\Http\Controllers;

use App\Enums\ChapterMemberRole;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A group's member list, where organisers choose who else organises the group.
 */
class ChapterMemberController extends Controller
{
    public function index(Request $request, Chapter $chapter): Response
    {
        Gate::authorize('organise', $chapter);

        $members = $chapter->members()
            ->orderByRaw('case when chapter_user.role = ? then 0 else 1 end', [ChapterMemberRole::Admin->value])
            ->orderBy('name')
            ->get(['users.id', 'users.name'])
            ->map(fn (User $member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'is_organiser' => $member->pivot->role === ChapterMemberRole::Admin,
                'is_you' => $member->id === $request->user()->id,
                'joined_at' => $member->pivot->joined_at?->toIso8601String(),
            ]);

        return Inertia::render('chapters/Members', [
            'group' => ['name' => $chapter->name, 'slug' => $chapter->slug],
            'members' => $members,
        ]);
    }
}
