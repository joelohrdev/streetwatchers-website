<?php

namespace App\Http\Controllers;

use App\Enums\ChapterStatus;
use App\Models\Chapter;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * How many upcoming meetups to show on the homepage.
     */
    private const UPCOMING_MEETUPS = 6;

    /**
     * The marketing homepage.
     */
    public function __invoke(): Response
    {
        $activeChapters = Chapter::query()->where('status', ChapterStatus::Active);

        return Inertia::render('Home', [
            'chapterStats' => [
                'chapterCount' => (clone $activeChapters)->count(),
                'countryCount' => (clone $activeChapters)->distinct()->count('country'),
            ],
            'upcomingMeetups' => Event::query()
                ->whereHas('chapter', fn (Builder $query) => $query->where('status', ChapterStatus::Active))
                ->whereNull('canceled_at')
                ->where('starts_at', '>=', now())
                ->with('chapter:id,name,slug,city,country')
                ->orderBy('starts_at')
                ->limit(self::UPCOMING_MEETUPS)
                ->get()
                ->map(fn (Event $event): array => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'starts_at' => $event->starts_at->toIso8601String(),
                    'ends_at' => $event->ends_at->toIso8601String(),
                    'timezone' => $event->timezone,
                    'group' => [
                        'name' => $event->chapter->name,
                        'slug' => $event->chapter->slug,
                        'city' => $event->chapter->city,
                        'country' => $event->chapter->country->label(),
                    ],
                ]),
        ]);
    }
}
