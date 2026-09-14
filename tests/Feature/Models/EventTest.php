<?php

use App\Enums\EventRsvpStatus;
use App\Models\Event;
use App\Models\User;

it('exposes the rsvp status on the event_rsvps pivot as an enum', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();

    $event->rsvps()->attach($user, ['status' => EventRsvpStatus::Interested]);

    expect($user->rsvpedEvents()->sole()->pivot->status)->toBe(EventRsvpStatus::Interested);
});
