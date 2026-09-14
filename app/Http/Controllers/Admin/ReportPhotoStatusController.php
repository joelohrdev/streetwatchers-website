<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\PhotoStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePhotoStatusRequest;
use App\Models\AuditLog;
use App\Models\Photo;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReportPhotoStatusController extends Controller
{
    /**
     * Flag or remove the photo a report is about, and mark the report as actioned.
     */
    public function update(UpdatePhotoStatusRequest $request, Report $report): RedirectResponse
    {
        $photo = $report->reportable;

        abort_unless($photo instanceof Photo, 404);

        $newStatus = PhotoStatus::from($request->validated('status'));
        $reason = $request->validated('reason');

        DB::transaction(function () use ($request, $report, $photo, $newStatus, $reason): void {
            $oldPhotoStatus = $photo->status;
            $photo->update(['status' => $newStatus]);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::PhotoStatusUpdated,
                subject: $photo,
                oldStatus: $oldPhotoStatus,
                newStatus: $newStatus,
                reason: $reason,
                metadata: ['report_id' => $report->id],
            );

            if ($report->status !== ReportStatus::Actioned) {
                $oldReportStatus = $report->status;
                $report->update(['status' => ReportStatus::Actioned]);

                AuditLog::record(
                    actor: $request->user(),
                    action: AuditAction::ReportStatusUpdated,
                    subject: $report,
                    oldStatus: $oldReportStatus,
                    newStatus: ReportStatus::Actioned,
                    reason: $reason,
                );
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Photo marked as :status.', ['status' => Str::lower($newStatus->label())])]);

        return back();
    }
}
