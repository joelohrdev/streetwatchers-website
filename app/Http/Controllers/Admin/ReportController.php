<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\PhotoStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * The kinds of content that can be reported, keyed by morph alias.
     *
     * @var array<string, string>
     */
    private const REPORTABLE_TYPES = [
        'photo' => 'Photo',
        'comment' => 'Comment',
        'article' => 'Article',
        'user' => 'User',
    ];

    public function index(Request $request): Response
    {
        $status = $request->enum('status', ReportStatus::class);
        $type = array_key_exists($request->string('type')->toString(), self::REPORTABLE_TYPES)
            ? $request->string('type')->toString()
            : null;

        $reports = Report::query()
            ->with(['reportable', 'reporter'])
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($type, fn (Builder $query) => $query->where('reportable_type', $type))
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Report $report): array => [
                'id' => $report->id,
                'reportable' => $this->summarize($report),
                'reason' => Str::limit($report->reason, 140),
                'status' => $report->status->value,
                'is_public_submission' => $report->is_public_submission,
                'reporter' => $report->reporter_id !== null ? $report->reporter->name : $report->reporter_email,
                'created_at' => $report->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/reports/Index', [
            'reports' => $reports,
            'filters' => ['status' => $status?->value, 'type' => $type],
            'statuses' => ReportStatus::options(),
            'types' => collect(self::REPORTABLE_TYPES)
                ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
                ->values(),
        ]);
    }

    public function show(Report $report): Response
    {
        $report->load(['reportable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
            Photo::class => ['user'],
            Comment::class => ['user'],
            Article::class => ['user'],
        ]), 'reporter']);

        $history = AuditLog::query()
            ->with('actor')
            ->where(fn (Builder $query) => $query->whereMorphedTo('subject', $report)
                ->when($report->reportable, fn (Builder $query, $reportable) => $query->orWhereMorphedTo('subject', $reportable)))
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (AuditLog $log): array => $log->toTimelineEntry());

        return Inertia::render('admin/reports/Show', [
            'report' => [
                'id' => $report->id,
                'reason' => $report->reason,
                'status' => $report->status->value,
                'is_public_submission' => $report->is_public_submission,
                'reporter' => $report->reporter
                    ? ['name' => $report->reporter->name, 'email' => $report->reporter->email]
                    : ($report->reporter_email ? ['name' => null, 'email' => $report->reporter_email] : null),
                'created_at' => $report->created_at?->toIso8601String(),
            ],
            'reportable' => $this->detail($report),
            'history' => $history,
            'statuses' => ReportStatus::options(),
            'photoStatuses' => array_values(array_filter(
                PhotoStatus::options(),
                fn (array $option): bool => in_array($option['value'], [PhotoStatus::Flagged->value, PhotoStatus::Removed->value], true),
            )),
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(ReportStatus::class)],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $newStatus = ReportStatus::from($validated['status']);
        $oldStatus = $report->status;

        if ($newStatus === $oldStatus) {
            return back();
        }

        DB::transaction(function () use ($request, $report, $oldStatus, $newStatus, $validated): void {
            $report->update(['status' => $newStatus]);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::ReportStatusUpdated,
                subject: $report,
                oldStatus: $oldStatus,
                newStatus: $newStatus,
                reason: $validated['reason'] ?? null,
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Report marked as :status.', ['status' => Str::lower($newStatus->label())])]);

        return back();
    }

    /**
     * A one-line description of the reported content for list views.
     *
     * @return array{type: string, type_label: string, id: int, label: string}
     */
    private function summarize(Report $report): array
    {
        $reportable = $report->reportable;

        return [
            'type' => $report->reportable_type,
            'type_label' => self::REPORTABLE_TYPES[$report->reportable_type] ?? Str::headline($report->reportable_type),
            'id' => $report->reportable_id,
            'label' => match (true) {
                $reportable instanceof Photo => $reportable->title ?? "Photo #{$reportable->id}",
                $reportable instanceof Comment => Str::limit($reportable->body, 80),
                $reportable instanceof Article => $reportable->title,
                $reportable instanceof User => $reportable->name,
                default => 'Deleted content',
            },
        ];
    }

    /**
     * Everything a moderator needs to judge the reported content.
     *
     * @return array<string, mixed>|null
     */
    private function detail(Report $report): ?array
    {
        $reportable = $report->reportable;

        return match (true) {
            $reportable instanceof Photo => [
                'type' => 'photo',
                'id' => $reportable->id,
                'title' => $reportable->title ?? "Photo #{$reportable->id}",
                'caption' => $reportable->caption,
                'context_story' => $reportable->context_story,
                'image_url' => Storage::disk('public')->url($reportable->image_path),
                'location_name' => $reportable->location_name,
                'consent_type' => $reportable->consent_type->label(),
                'status' => $reportable->status->value,
                'author' => $reportable->user->name,
            ],
            $reportable instanceof Comment => [
                'type' => $report->reportable_type,
                'id' => $reportable->id,
                'title' => self::REPORTABLE_TYPES[$report->reportable_type],
                'body' => $reportable->body,
                'author' => $reportable->user->name,
            ],
            $reportable instanceof Article => [
                'type' => 'article',
                'id' => $reportable->id,
                'title' => $reportable->title,
                'body' => Str::limit($reportable->body, 1000),
                'author' => $reportable->user->name,
                'published_at' => $reportable->published_at?->toIso8601String(),
            ],
            $reportable instanceof User => [
                'type' => 'user',
                'id' => $reportable->id,
                'title' => $reportable->name,
                'email' => $reportable->email,
                'status' => $reportable->status->value,
            ],
            default => null,
        };
    }
}
