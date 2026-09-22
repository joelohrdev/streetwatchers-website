<?php

namespace App\Http\Controllers;

use App\Enums\ChapterStatus;
use App\Http\Requests\StoreEventRequest;
use App\Models\Chapter;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Meetups: photo walks and other events a group runs. They are called "meetups" on the public site.
 */
class EventController extends Controller
{
    /**
     * A public meetup page, so people who aren't members yet can see what the group does and RSVP.
     */
    public function show(Request $request, Chapter $chapter, Event $event): Response
    {
        abort_unless($chapter->status === ChapterStatus::Active, 404);

        $user = $request->user();
        $canManage = $user !== null && $chapter->isOrganizedBy($user);
        $event->loadCount('attendees')->load('organizer:id,name,instagram_handle');

        return Inertia::render('meetups/Show', [
            'group' => [
                'name' => $chapter->name,
                'slug' => $chapter->slug,
                'city' => $chapter->city,
                'country' => $chapter->country->label(),
            ],
            'meetup' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location_name' => $event->location_name,
                'starts_at' => $event->starts_at->toIso8601String(),
                'ends_at' => $event->ends_at->toIso8601String(),
                'timezone' => $event->timezone,
                'organizer' => [
                    'name' => $event->organizer->name,
                    'instagram_handle' => $event->organizer->instagram_handle,
                ],
                'rsvps_enabled' => $event->rsvps_enabled,
                'rsvp_limit' => $event->rsvp_limit,
                'attendees_count' => $event->attendees_count,
                'is_canceled' => $event->isCanceled(),
                'has_ended' => $event->hasEnded(),
                'is_accepting_rsvps' => $event->isAcceptingRsvps(),
                'share_url' => $event->shareUrl(),
            ],
            'viewer' => [
                'is_guest' => $user === null,
                'is_member' => $user !== null && $chapter->members()->whereKey($user->id)->exists(),
                'is_going' => $user !== null && $event->attendees()->whereKey($user->id)->exists(),
                'can_manage' => $canManage,
            ],
            // Only organizers see who is coming. Everyone else sees the count.
            'attendees' => $canManage
                ? $event->attendees()->orderBy('name')->pluck('name')
                : null,
            'status' => $request->session()->get('status'),
        ])->withViewData(['meta' => [
            'title' => ($event->isCanceled() ? 'Canceled: ' : '').$event->title,
            'description' => $this->previewDescription($chapter, $event),
            'url' => route('chapters.events.show', [$chapter, $event]),
        ]]);
    }

    /**
     * "Sat, Oct 4, 10:00 AM BST at Riverside Museum, with Glasgow Streetwatchers." then the description, for
     * link previews. The time is in the meetup's own time zone.
     */
    private function previewDescription(Chapter $chapter, Event $event): string
    {
        $when = $event->starts_at->timezone($event->timezone)->format('D, M j, g:i A T');

        return Str::of("{$when} at {$event->location_name}, with {$chapter->name}. {$event->description}")
            ->squish()
            ->limit(200)
            ->toString();
    }

    public function create(Chapter $chapter): Response
    {
        Gate::authorize('organize', $chapter);

        return Inertia::render('meetups/Create', [
            'group' => ['name' => $chapter->name, 'slug' => $chapter->slug],
        ]);
    }

    public function store(StoreEventRequest $request, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('organize', $chapter);

        $event = $chapter->events()->create([
            ...$request->meetupAttributes(),
            'organizer_id' => $request->user()->id,
        ]);

        return to_route('chapters.events.show', [$chapter, $event])->with('status', 'meetup-created');
    }

    public function edit(Chapter $chapter, Event $event): Response
    {
        Gate::authorize('update', $event);
        $this->ensureEditable($event);

        $startsAt = $event->starts_at->timezone($event->timezone);

        return Inertia::render('meetups/Edit', [
            'group' => ['name' => $chapter->name, 'slug' => $chapter->slug],
            'meetup' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location_name' => $event->location_name,
                'date' => $startsAt->format('Y-m-d'),
                'starts_at_time' => $startsAt->format('H:i'),
                'ends_at_time' => $event->ends_at->timezone($event->timezone)->format('H:i'),
                'timezone' => $event->timezone,
                'rsvps_enabled' => $event->rsvps_enabled,
                'rsvp_limit' => $event->rsvp_limit,
            ],
        ]);
    }

    public function update(StoreEventRequest $request, Chapter $chapter, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);
        $this->ensureEditable($event);

        $event->update($request->meetupAttributes());

        return to_route('chapters.events.show', [$chapter, $event])->with('status', 'meetup-updated');
    }

    /**
     * Canceled and finished meetups are kept as a record and can't be changed.
     */
    private function ensureEditable(Event $event): void
    {
        abort_if($event->isCanceled() || $event->hasEnded(), 403);
    }
}
