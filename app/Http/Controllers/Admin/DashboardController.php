<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChapterStatus;
use App\Enums\PhotoStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterUser;
use App\Models\Collective;
use App\Models\CollectiveApplication;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'users' => User::query()->count(),
                'collectives' => Collective::query()->count(),
                'openReports' => Report::query()->where('status', ReportStatus::Open)->count(),
                'chapters' => $this->countByStatus(Chapter::query(), ChapterStatus::cases()),
                'photos' => $this->countByStatus(Photo::query(), PhotoStatus::cases()),
            ],
            'activity' => [
                $this->countRecent('signups', 'New users', User::query(), 'created_at'),
                $this->countRecent('groupRequests', 'Groups started', Chapter::query(), 'created_at'),
                $this->countRecent('groupJoins', 'Group joins', ChapterUser::query(), 'joined_at'),
                $this->countRecent('meetups', 'Meetups created', Event::query(), 'created_at'),
                $this->countRecent('collectiveApplications', 'Collective applications', CollectiveApplication::query(), 'created_at'),
            ],
        ]);
    }

    /**
     * Count rows created in the last 7 and 30 days in one query.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return array{key: string, label: string, lastWeek: int, lastMonth: int}
     */
    private function countRecent(string $key, string $label, Builder $query, string $column): array
    {
        $counts = $query->toBase()
            ->where($column, '>=', now()->subDays(30))
            ->selectRaw("count(*) as last_month, count(case when {$column} >= ? then 1 end) as last_week", [now()->subDays(7)])
            ->first();

        return [
            'key' => $key,
            'label' => $label,
            'lastWeek' => (int) $counts->last_week,
            'lastMonth' => (int) $counts->last_month,
        ];
    }

    /**
     * Count rows per status in one grouped query, including statuses with no rows.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<ChapterStatus>|list<PhotoStatus>  $statuses
     * @return list<array{value: string, label: string, count: int}>
     */
    private function countByStatus(Builder $query, array $statuses): array
    {
        $counts = $query->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return array_map(fn (ChapterStatus|PhotoStatus $status): array => [
            'value' => $status->value,
            'label' => $status->label(),
            'count' => (int) ($counts[$status->value] ?? 0),
        ], $statuses);
    }
}
