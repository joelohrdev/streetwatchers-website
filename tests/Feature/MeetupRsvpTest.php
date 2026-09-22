<?php

use App\Enums\ChapterMemberRole;
use App\Enums\EventRsvpStatus;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a member can RSVP to a meetup', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();

    $this->actingAs($member)
        ->post(route('chapters.events.rsvp.store', [$group, $meetup]))
        ->assertRedirect(route('chapters.events.show', [$group, $meetup]))
        ->assertSessionHas('status', 'rsvp-going');

    expect($meetup->attendees()->pluck('users.id')->all())->toBe([$member->id]);
});

test('RSVPing to a meetup joins its group', function () {
    $group = Chapter::factory()->active()->create();
    $visitor = User::factory()->create();
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();

    $this->actingAs($visitor)
        ->post(route('chapters.events.rsvp.store', [$group, $meetup]))
        ->assertSessionHas('status', 'rsvp-going-joined');

    expect($group->members()->whereKey($visitor->id)->sole()->pivot->role)->toBe(ChapterMemberRole::Member)
        ->and($meetup->attendees()->whereKey($visitor->id)->exists())->toBeTrue();
});

test('an organiser who RSVPs keeps their organiser role', function () {
    $group = Chapter::factory()->active()->create();
    $organiser = User::factory()->create();
    $group->members()->attach($organiser, ['role' => ChapterMemberRole::Admin]);
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();

    $this->actingAs($organiser)->post(route('chapters.events.rsvp.store', [$group, $meetup]));

    expect($group->members()->whereKey($organiser->id)->sole()->pivot->role)->toBe(ChapterMemberRole::Admin);
});

test('RSVPing twice keeps one RSVP and still counts when the meetup is full', function () {
    $group = Chapter::factory()->active()->create();
    $user = User::factory()->create();
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps(1)->create();

    $this->actingAs($user)->post(route('chapters.events.rsvp.store', [$group, $meetup]));
    $this->actingAs($user)
        ->post(route('chapters.events.rsvp.store', [$group, $meetup]))
        ->assertSessionHasNoErrors();

    expect($meetup->attendees()->count())->toBe(1);
});

test('nobody can RSVP once a meetup is full', function () {
    $group = Chapter::factory()->active()->create();
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps(1)->create();
    $meetup->rsvps()->attach(User::factory()->create(), ['status' => EventRsvpStatus::Going]);
    $latecomer = User::factory()->create();

    $this->actingAs($latecomer)
        ->post(route('chapters.events.rsvp.store', [$group, $meetup]))
        ->assertSessionHasErrors(['rsvp' => 'This meetup is full.']);

    expect($meetup->attendees()->count())->toBe(1)
        ->and($group->members()->whereKey($latecomer->id)->exists())->toBeFalse();
});

test('nobody can RSVP to a meetup that is not taking RSVPs', function (Closure $makeMeetup, string $message) {
    $group = Chapter::factory()->active()->create();
    $meetup = $makeMeetup($group);

    $this->actingAs(User::factory()->create())
        ->post(route('chapters.events.rsvp.store', [$group, $meetup]))
        ->assertSessionHasErrors(['rsvp' => $message]);

    expect($meetup->attendees()->count())->toBe(0);
})->with([
    'RSVPs turned off' => [
        fn (Chapter $group) => Event::factory()->for($group)->upcoming()->create(),
        'This meetup doesn’t take RSVPs. Just turn up.',
    ],
    'cancelled' => [
        fn (Chapter $group) => Event::factory()->for($group)->upcoming()->takingRsvps()->cancelled()->create(),
        'This meetup has been cancelled.',
    ],
    'already happened' => [
        fn (Chapter $group) => Event::factory()->for($group)->takingRsvps()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subDay()->addHours(2),
        ]),
        'This meetup has already happened.',
    ],
]);

test('cancelling an RSVP keeps the person in the group', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();
    $meetup->rsvps()->attach($member, ['status' => EventRsvpStatus::Going]);

    $this->actingAs($member)
        ->delete(route('chapters.events.rsvp.destroy', [$group, $meetup]))
        ->assertSessionHas('status', 'rsvp-cancelled');

    expect($meetup->attendees()->count())->toBe(0)
        ->and($group->members()->whereKey($member->id)->exists())->toBeTrue();
});

test('guests must log in to RSVP', function () {
    $group = Chapter::factory()->active()->create();
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();

    $this->post(route('chapters.events.rsvp.store', [$group, $meetup]))->assertRedirect(route('login'));

    expect($meetup->attendees()->count())->toBe(0);
});

test('a guest who logs in to RSVP is brought back to the meetup', function () {
    $group = Chapter::factory()->active()->create();
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();
    $user = User::factory()->create();

    $this->get(route('chapters.events.rsvp.create', [$group, $meetup, 'via' => 'login']))
        ->assertRedirect(route('login'));

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('chapters.events.show', [$group, $meetup]));
});

test('a guest can choose to register before RSVPing', function () {
    $group = Chapter::factory()->active()->create();
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();

    $this->get(route('chapters.events.rsvp.create', [$group, $meetup, 'via' => 'register']))
        ->assertRedirect(route('register'));
});

test('only organisers see who is going', function (?ChapterMemberRole $role, bool $seesNames) {
    $group = Chapter::factory()->active()->create();
    $viewer = User::factory()->create();

    if ($role !== null) {
        $group->members()->attach($viewer, ['role' => $role]);
    }

    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();
    $meetup->rsvps()->attach(User::factory()->create(['name' => 'Ana Walker']), ['status' => EventRsvpStatus::Going]);

    $this->actingAs($viewer)
        ->get(route('chapters.events.show', [$group, $meetup]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('meetup.attendees_count', 1)
            ->where('attendees', $seesNames ? ['Ana Walker'] : null));
})->with([
    'not in the group' => [null, false],
    'member' => [ChapterMemberRole::Member, false],
    'organiser' => [ChapterMemberRole::Admin, true],
]);
