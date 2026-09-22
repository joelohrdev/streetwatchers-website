<?php

namespace App\Http\Controllers;

use App\Enums\ChapterStatus;
use App\Http\Requests\StoreEventRequest;
use App\Models\Chapter;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        $canManage = $user !== null && $chapter->isOrganisedBy($user);
        $event->loadCount('attendees')->load('organizer:id,name');

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
                'organizer' => $event->organizer->name,
                'rsvps_enabled' => $event->rsvps_enabled,
                'rsvp_limit' => $event->rsvp_limit,
                'attendees_count' => $event->attendees_count,
                'is_cancelled' => $event->isCancelled(),
                'has_ended' => $event->hasEnded(),
                'is_accepting_rsvps' => $event->isAcceptingRsvps(),
            ],
            'viewer' => [
                'is_guest' => $user === null,
                'is_member' => $user !== null && $chapter->members()->whereKey($user->id)->exists(),
                'is_going' => $user !== null && $event->attendees()->whereKey($user->id)->exists(),
                'can_manage' => $canManage,
            ],
            // Only organisers see who is coming. Everyone else sees the count.
            'attendees' => $canManage
                ? $event->attendees()->orderBy('name')->pluck('name')
                : null,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function create(Chapter $chapter): Response
    {
        Gate::authorize('organise', $chapter);

        return Inertia::render('meetups/Create', [
            'group' => ['name' => $chapter->name, 'slug' => $chapter->slug],
        ]);
    }

    public function store(StoreEventRequest $request, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('organise', $chapter);

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
     * Cancelled and finished meetups are kept as a record and can't be changed.
     */
    private function ensureEditable(Event $event): void
    {
        abort_if($event->isCancelled() || $event->hasEnded(), 403);
    }
}
