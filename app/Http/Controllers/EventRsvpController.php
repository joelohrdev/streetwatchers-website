<?php

namespace App\Http\Controllers;

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Enums\EventRsvpStatus;
use App\Models\Chapter;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * RSVPing to a meetup. Going to a meetup also joins its group, so attendees hear about the next one.
 */
class EventRsvpController extends Controller
{
    /**
     * Send a guest to log in or register, then bring them back to the meetup to RSVP.
     */
    public function create(Request $request, Chapter $chapter, Event $event): RedirectResponse
    {
        abort_unless($chapter->status === ChapterStatus::Active, 404);

        $meetupPage = route('chapters.events.show', [$chapter, $event]);

        if ($request->user() !== null) {
            return redirect()->to($meetupPage);
        }

        redirect()->setIntendedUrl($meetupPage);

        return $request->query('via') === 'register'
            ? to_route('register')
            : to_route('login');
    }

    public function store(Request $request, Chapter $chapter, Event $event): RedirectResponse
    {
        abort_unless($chapter->status === ChapterStatus::Active, 404);

        $user = $request->user();

        $joinedGroup = DB::transaction(function () use ($chapter, $event, $user): bool {
            // Lock the meetup so two people can't take the last place at the same time.
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);

            if ($event->attendees()->whereKey($user->id)->exists()) {
                return false;
            }

            if (! $event->isAcceptingRsvps()) {
                throw ValidationException::withMessages(['rsvp' => $this->closedReason($event)]);
            }

            $event->rsvps()->syncWithoutDetaching([$user->id => ['status' => EventRsvpStatus::Going]]);

            if ($chapter->members()->whereKey($user->id)->exists()) {
                return false;
            }

            $chapter->members()->attach($user, ['role' => ChapterMemberRole::Member]);

            return true;
        });

        return to_route('chapters.events.show', [$chapter, $event])
            ->with('status', $joinedGroup ? 'rsvp-going-joined' : 'rsvp-going');
    }

    /**
     * Cancel an RSVP. The person stays in the group.
     */
    public function destroy(Request $request, Chapter $chapter, Event $event): RedirectResponse
    {
        $event->rsvps()->detach($request->user());

        return to_route('chapters.events.show', [$chapter, $event])->with('status', 'rsvp-canceled');
    }

    private function closedReason(Event $event): string
    {
        return match (true) {
            ! $event->rsvps_enabled => 'This meetup doesn’t take RSVPs. Just turn up.',
            $event->isCanceled() => 'This meetup has been canceled.',
            $event->hasEnded() => 'This meetup has already happened.',
            default => 'This meetup is full.',
        };
    }
}
