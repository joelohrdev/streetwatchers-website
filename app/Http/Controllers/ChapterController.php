<?php

namespace App\Http\Controllers;

use App\Concerns\GeneratesUniqueSlugs;
use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Enums\Country;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Mail\ChapterProposed;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ChapterController extends Controller
{
    use GeneratesUniqueSlugs;

    /**
     * How many nearby chapters to suggest on a chapter page.
     */
    private const NEARBY_CHAPTERS = 4;

    /**
     * How far away, in miles, a chapter can be and still be suggested as nearby.
     */
    private const NEARBY_RADIUS_MILES = 100;

    /**
     * Slugs that would collide with fixed routes under /chapters.
     *
     * @var list<string>
     */
    private const RESERVED_SLUGS = ['create'];

    /**
     * The public chapter directory. Distances are worked out in the browser so a visitor's location never reaches the server.
     */
    public function index(Request $request): Response
    {
        $chapters = Chapter::query()
            ->where('status', ChapterStatus::Active)
            ->withCount('members')
            ->orderBy('city')
            ->orderBy('id')
            ->get()
            ->map(fn (Chapter $chapter): array => [
                'id' => $chapter->id,
                'name' => $chapter->name,
                'slug' => $chapter->slug,
                'city' => $chapter->city,
                'country' => $chapter->country->label(),
                'latitude' => $chapter->latitude,
                'longitude' => $chapter->longitude,
                'description' => Str::limit($chapter->description, 160),
                'members_count' => $chapter->members_count,
            ]);

        return Inertia::render('chapters/Index', [
            'chapters' => $chapters,
            'search' => $request->string('q')->trim()->limit(100, '')->toString(),
        ]);
    }

    /**
     * A public chapter page. Only active chapters are visible.
     */
    public function show(Request $request, Chapter $chapter): Response
    {
        abort_unless($chapter->status === ChapterStatus::Active, 404);

        $chapter->loadCount('members');

        $nearby = Chapter::query()
            ->where('status', ChapterStatus::Active)
            ->whereKeyNot($chapter->id)
            ->get(['id', 'name', 'slug', 'city', 'country', 'latitude', 'longitude'])
            ->filter(fn (Chapter $other): bool => $chapter->distanceInMilesTo($other) <= self::NEARBY_RADIUS_MILES)
            ->map(fn (Chapter $other): array => [
                'id' => $other->id,
                'name' => $other->name,
                'slug' => $other->slug,
                'city' => $other->city,
                'country' => $other->country->label(),
                'latitude' => $other->latitude,
                'longitude' => $other->longitude,
                'distance' => round($chapter->distanceInKilometersTo($other)),
            ])
            ->sortBy('distance')
            ->take(self::NEARBY_CHAPTERS)
            ->values();

        return Inertia::render('chapters/Show', [
            'chapter' => [
                'id' => $chapter->id,
                'name' => $chapter->name,
                'slug' => $chapter->slug,
                'city' => $chapter->city,
                'country' => $chapter->country->label(),
                'latitude' => $chapter->latitude,
                'longitude' => $chapter->longitude,
                'description' => $chapter->description,
                'members_count' => $chapter->members_count,
                'created_at' => $chapter->created_at?->toIso8601String(),
                'share_url' => $chapter->shareUrl(),
            ],
            'organizers' => $chapter->members()
                ->wherePivot('role', ChapterMemberRole::Admin)
                ->orderBy('name')
                ->get(['users.name', 'users.instagram_handle'])
                ->map(fn (User $organizer): array => [
                    'name' => $organizer->name,
                    'instagram_handle' => $organizer->instagram_handle,
                ]),
            'upcomingEvents' => $chapter->events()
                ->where('ends_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get()
                ->map(fn (Event $event): array => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'location_name' => $event->location_name,
                    'starts_at' => $event->starts_at->toIso8601String(),
                    'ends_at' => $event->ends_at->toIso8601String(),
                    'timezone' => $event->timezone,
                    'is_canceled' => $event->isCanceled(),
                ]),
            'nearby' => $nearby,
            'nearbyRadiusMiles' => self::NEARBY_RADIUS_MILES,
            'membership' => $this->membershipFor($request, $chapter),
            'status' => $request->session()->get('status'),
        ])->withViewData(['meta' => [
            'title' => $chapter->name,
            'description' => Str::of($chapter->description)->squish()->limit(200)->toString(),
            'url' => route('chapters.show', $chapter),
        ]]);
    }

    /**
     * How the visitor relates to the group, which decides the join panel on the group page.
     *
     * @return 'guest'|'none'|'member'|'organizer'
     */
    private function membershipFor(Request $request, Chapter $chapter): string
    {
        $user = $request->user();

        if ($user === null) {
            return 'guest';
        }

        $role = $chapter->memberships()->where('user_id', $user->id)->first()?->role;

        return match ($role) {
            ChapterMemberRole::Admin => 'organizer',
            ChapterMemberRole::Member => 'member',
            default => 'none',
        };
    }

    /**
     * The form for an organizer to change their group's details.
     */
    public function edit(Chapter $chapter): Response
    {
        Gate::authorize('organize', $chapter);

        return Inertia::render('chapters/Edit', [
            'chapter' => [
                'name' => $chapter->name,
                'slug' => $chapter->slug,
                'description' => $chapter->description,
                'city' => $chapter->city,
                'country' => $chapter->country->value,
                'latitude' => $chapter->latitude,
                'longitude' => $chapter->longitude,
            ],
            'countries' => Country::options(),
        ]);
    }

    /**
     * Save an organizer's changes. The slug stays the same, so links and QR codes already shared keep working.
     */
    public function update(UpdateChapterRequest $request, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('organize', $chapter);

        $chapter->update($request->safe()->only(['name', 'city', 'country', 'latitude', 'longitude', 'description']));

        return to_route('chapters.show', $chapter)->with('status', 'group-updated');
    }

    /**
     * Show the form for proposing a new chapter.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('chapters/Create', [
            'submittedChapter' => $request->session()->get('submitted_chapter'),
            'countries' => Country::options(),
        ]);
    }

    /**
     * Create a pending chapter with the proposer as its first admin.
     */
    public function store(StoreChapterRequest $request): RedirectResponse
    {
        $chapter = DB::transaction(function () use ($request): Chapter {
            $chapter = Chapter::query()->create([
                ...$request->safe()->only(['name', 'city', 'country', 'latitude', 'longitude', 'description']),
                'slug' => $this->uniqueSlug($request->validated('name'), Chapter::class, self::RESERVED_SLUGS),
                'status' => ChapterStatus::Pending,
            ]);

            $chapter->members()->attach($request->user(), ['role' => ChapterMemberRole::Admin]);

            return $chapter;
        });

        $siteAdmins = User::query()
            ->where('role', UserRole::SuperAdmin)
            ->where('status', UserStatus::Active)
            ->pluck('email')
            ->all();

        if ($siteAdmins !== []) {
            Mail::to($siteAdmins)->queue(new ChapterProposed($chapter, $request->user()));
        }

        return to_route('chapters.create')->with('submitted_chapter', $chapter->name);
    }
}
