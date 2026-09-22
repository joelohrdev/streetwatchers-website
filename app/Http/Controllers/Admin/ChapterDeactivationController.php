<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\ChapterStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeactivateChapterRequest;
use App\Mail\ChapterDeactivated;
use App\Models\AuditLog;
use App\Models\Chapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ChapterDeactivationController extends Controller
{
    public function store(DeactivateChapterRequest $request, Chapter $chapter): RedirectResponse
    {
        if ($chapter->status !== ChapterStatus::Active) {
            throw ValidationException::withMessages([
                'chapter' => 'Only active chapters can be deactivated.',
            ]);
        }

        DB::transaction(function () use ($request, $chapter): void {
            $chapter->update(['status' => ChapterStatus::Inactive]);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::ChapterDeactivated,
                subject: $chapter,
                oldStatus: ChapterStatus::Active,
                newStatus: ChapterStatus::Inactive,
                reason: $request->validated('reason'),
            );
        });

        $chapter->mailOrganizers(new ChapterDeactivated($chapter));

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name deactivated.', ['name' => $chapter->name])]);

        return back();
    }
}
