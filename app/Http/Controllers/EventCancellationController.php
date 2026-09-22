<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Cancelling a meetup. It stays visible, marked as cancelled, so people who planned to go find out.
 */
class EventCancellationController extends Controller
{
    public function store(Chapter $chapter, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);
        abort_if($event->isCancelled() || $event->hasEnded(), 403);

        $event->update(['cancelled_at' => now()]);

        return to_route('chapters.events.show', [$chapter, $event])->with('status', 'meetup-cancelled');
    }
}
