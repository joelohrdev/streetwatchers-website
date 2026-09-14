<?php

namespace App\Http\Controllers;

use App\Concerns\GeneratesUniqueSlugs;
use App\Enums\CollectiveApplicationStatus;
use App\Enums\CollectiveMemberRole;
use App\Enums\PhotoStatus;
use App\Http\Requests\StoreCollectiveRequest;
use App\Http\Requests\UpdateCollectiveRequest;
use App\Models\Collective;
use App\Models\CollectiveUser;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CollectiveController extends Controller
{
    use GeneratesUniqueSlugs;

    /**
     * Slugs that would collide with fixed routes under /collectives.
     *
     * @var list<string>
     */
    private const RESERVED_SLUGS = ['create'];

    /**
     * The public directory. Verified collectives come first, then the newest.
     */
    public function index(): Response
    {
        $collectives = Collective::query()
            ->withCount('members')
            ->orderByDesc('is_verified')
            ->latest()
            ->orderByDesc('id')
            ->get()
            ->map(fn (Collective $collective): array => [
                'id' => $collective->id,
                'name' => $collective->name,
                'slug' => $collective->slug,
                'excerpt' => Str::limit($collective->description, 140),
                'based_in' => $collective->based_in,
                'logo_url' => $collective->logoUrl(),
                'is_verified' => $collective->is_verified,
                'is_open_for_applications' => $collective->is_open_for_applications,
                'members_count' => $collective->members_count,
            ]);

        return Inertia::render('collectives/Index', [
            'collectives' => $collectives,
        ]);
    }

    public function show(Request $request, Collective $collective): Response
    {
        $memberships = $collective->memberships()->with('user')->get()
            ->sortBy(fn (CollectiveUser $membership): string => $membership->user->name)
            ->values();

        $recentPhotos = Photo::query()
            ->with('user')
            ->where('status', PhotoStatus::Published)
            ->whereIn('user_id', $memberships->pluck('user_id'))
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Photo $photo): array => [
                'id' => $photo->id,
                'title' => $photo->title ?? "Photo by {$photo->user->name}",
                'image_url' => Storage::disk('public')->url($photo->image_path),
                'author' => $photo->user->name,
            ]);

        $viewer = $this->viewerState($request, $collective);

        return Inertia::render('collectives/Show', [
            'collective' => [
                'id' => $collective->id,
                'name' => $collective->name,
                'slug' => $collective->slug,
                'description' => $collective->description,
                'based_in' => $collective->based_in,
                'website_url' => $collective->website_url,
                'instagram_url' => $collective->instagram_url,
                'logo_url' => $collective->logoUrl(),
                'is_verified' => $collective->is_verified,
                'is_open_for_applications' => $collective->is_open_for_applications,
                'members_count' => $memberships->count(),
                'created_at' => $collective->created_at?->toIso8601String(),
            ],
            'founders' => $memberships
                ->filter(fn (CollectiveUser $membership): bool => $membership->role === CollectiveMemberRole::Founder)
                ->map(fn (CollectiveUser $membership): string => $membership->user->name)
                ->values(),
            'members' => $memberships
                ->filter(fn (CollectiveUser $membership): bool => $membership->role === CollectiveMemberRole::Member)
                ->map(fn (CollectiveUser $membership): string => $membership->user->name)
                ->values(),
            'recentPhotos' => $recentPhotos,
            'viewer' => $viewer,
            'pendingApplicationsCount' => $viewer === 'founder'
                ? $collective->applications()->where('status', CollectiveApplicationStatus::Pending)->count()
                : null,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('collectives/Create');
    }

    /**
     * Create a collective with the member who started it as its founder.
     */
    public function store(StoreCollectiveRequest $request): RedirectResponse
    {
        $collective = DB::transaction(function () use ($request): Collective {
            $collective = Collective::query()->create([
                ...$request->safe()->only(['name', 'description', 'based_in', 'website_url', 'instagram_url']),
                'slug' => $this->uniqueSlug($request->validated('name'), Collective::class, self::RESERVED_SLUGS),
                'logo_path' => $request->file('logo')?->store('collective-logos', 'public'),
                'is_open_for_applications' => $request->boolean('is_open_for_applications'),
            ]);

            $collective->members()->attach($request->user(), ['role' => CollectiveMemberRole::Founder]);

            return $collective;
        });

        return to_route('collectives.show', $collective)->with('status', 'collective-created');
    }

    public function edit(Collective $collective): Response
    {
        Gate::authorize('update', $collective);

        return Inertia::render('collectives/Edit', [
            'collective' => [
                'name' => $collective->name,
                'slug' => $collective->slug,
                'description' => $collective->description,
                'based_in' => $collective->based_in,
                'website_url' => $collective->website_url,
                'instagram_url' => $collective->instagram_url,
                'logo_url' => $collective->logoUrl(),
                'is_open_for_applications' => $collective->is_open_for_applications,
            ],
        ]);
    }

    /**
     * Update the collective's details. The slug stays the same so existing links keep working.
     */
    public function update(UpdateCollectiveRequest $request, Collective $collective): RedirectResponse
    {
        Gate::authorize('update', $collective);

        $attributes = [
            ...$request->safe()->only(['name', 'description', 'based_in', 'website_url', 'instagram_url']),
            'is_open_for_applications' => $request->boolean('is_open_for_applications'),
        ];

        if ($request->hasFile('logo') || $request->boolean('remove_logo')) {
            if ($collective->logo_path !== null) {
                Storage::disk('public')->delete($collective->logo_path);
            }

            $attributes['logo_path'] = $request->file('logo')?->store('collective-logos', 'public');
        }

        $collective->update($attributes);

        return to_route('collectives.show', $collective)->with('status', 'collective-updated');
    }

    /**
     * How the visitor relates to the collective, which decides the join panel on its page.
     *
     * @return 'guest'|'founder'|'member'|'pending'|'closed'|'declined'|'open'
     */
    private function viewerState(Request $request, Collective $collective): string
    {
        $user = $request->user();

        if ($user === null) {
            return 'guest';
        }

        $role = $collective->roleOf($user);

        if ($role !== null) {
            return $role === CollectiveMemberRole::Founder ? 'founder' : 'member';
        }

        if ($collective->hasPendingApplicationFrom($user)) {
            return 'pending';
        }

        if (! $collective->is_open_for_applications) {
            return 'closed';
        }

        $latestApplication = $collective->applications()->where('user_id', $user->id)->latest('id')->first();

        return $latestApplication?->status === CollectiveApplicationStatus::Declined ? 'declined' : 'open';
    }
}
