<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\ChapterUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChapterController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->enum('status', ChapterStatus::class);

        $chapters = Chapter::query()
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->withCount([
                'members',
                'members as admins_count' => fn (Builder $query) => $query->where('chapter_user.role', ChapterMemberRole::Admin),
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Chapter $chapter): array => [
                'id' => $chapter->id,
                'name' => $chapter->name,
                'slug' => $chapter->slug,
                'city' => $chapter->city,
                'country' => $chapter->country->label(),
                'status' => $chapter->status->value,
                'members_count' => $chapter->members_count,
                'admins_count' => $chapter->admins_count,
                'created_at' => $chapter->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/chapters/Index', [
            'chapters' => $chapters,
            'filters' => ['status' => $status?->value],
            'statuses' => ChapterStatus::options(),
        ]);
    }

    public function show(Chapter $chapter): Response
    {
        return Inertia::render('admin/chapters/Show', [
            'chapter' => [
                'id' => $chapter->id,
                'name' => $chapter->name,
                'slug' => $chapter->slug,
                'city' => $chapter->city,
                'country' => $chapter->country->label(),
                'description' => $chapter->description,
                'status' => $chapter->status->value,
                'created_at' => $chapter->created_at?->toIso8601String(),
            ],
            'members' => $chapter->memberships()
                ->with('user')
                ->get()
                ->sortBy(fn (ChapterUser $membership): string => $membership->user->name)
                ->values()
                ->map(fn (ChapterUser $membership): array => [
                    'id' => $membership->user->id,
                    'name' => $membership->user->name,
                    'email' => $membership->user->email,
                    'role' => $membership->role->value,
                    'joined_at' => $membership->joined_at->toIso8601String(),
                ]),
            'history' => $chapter->auditLogs()
                ->with('actor')
                ->latest('id')
                ->limit(50)
                ->get()
                ->map(fn (AuditLog $log): array => $log->toTimelineEntry()),
            'statuses' => ChapterStatus::options(),
            'requiredAdmins' => ChapterApprovalController::REQUIRED_ADMINS,
        ]);
    }
}
