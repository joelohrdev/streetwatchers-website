<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Canceling a meetup. It stays visible, marked as canceled, so people who planned to go find out.
 */
class EventCancellationController extends Controller
{
    public function store(Chapter $chapter, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);
        abort_if($event->isCanceled() || $event->hasEnded(), 403);

        $event->update(['canceled_at' => now()]);

        return to_route('chapters.events.show', [$chapter, $event])->with('status', 'meetup-canceled');
    }
}
