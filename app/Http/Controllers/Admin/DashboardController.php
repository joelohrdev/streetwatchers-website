<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChapterStatus;
use App\Enums\PhotoStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Collective;
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
        ]);
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
