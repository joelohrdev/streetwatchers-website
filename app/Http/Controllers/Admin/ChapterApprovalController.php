<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Http\Controllers\Controller;
use App\Mail\ChapterApproved;
use App\Models\AuditLog;
use App\Models\Chapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ChapterApprovalController extends Controller
{
    /**
     * The number of chapter admins a pending chapter needs before it can be approved.
     */
    public const REQUIRED_ADMINS = 1;

    public function store(Request $request, Chapter $chapter): RedirectResponse
    {
        if ($chapter->status !== ChapterStatus::Pending) {
            throw ValidationException::withMessages([
                'chapter' => 'Only pending chapters can be approved.',
            ]);
        }

        $adminCount = $chapter->members()->wherePivot('role', ChapterMemberRole::Admin)->count();

        if ($adminCount < self::REQUIRED_ADMINS) {
            throw ValidationException::withMessages([
                'chapter' => sprintf(
                    'A chapter needs at least %d %s before it can be approved. This chapter has %d.',
                    self::REQUIRED_ADMINS,
                    Str::plural('admin', self::REQUIRED_ADMINS),
                    $adminCount,
                ),
            ]);
        }

        DB::transaction(function () use ($request, $chapter): void {
            $chapter->update(['status' => ChapterStatus::Active]);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::ChapterApproved,
                subject: $chapter,
                oldStatus: ChapterStatus::Pending,
                newStatus: ChapterStatus::Active,
            );
        });

        $chapter->mailOrganizers(new ChapterApproved($chapter));

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name approved.', ['name' => $chapter->name])]);

        return back();
    }
}
