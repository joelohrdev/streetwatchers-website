<?php

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function meetupDetails(array $overrides = []): array
{
    return [
        'title' => 'Sunday walk along the Clyde',
        'description' => 'Meet at the museum, then west along the river.',
        'location_name' => 'Riverside Museum entrance',
        'date' => '2026-10-04',
        'starts_at_time' => '10:00',
        'ends_at_time' => '12:30',
        'timezone' => 'Europe/London',
        'rsvps_enabled' => '1',
        'rsvp_limit' => '12',
        ...$overrides,
    ];
}

function groupWithOrganiser(): array
{
    $group = Chapter::factory()->active()->create();
    $organiser = User::factory()->create();
    $group->members()->attach($organiser, ['role' => ChapterMemberRole::Admin]);

    return [$group, $organiser];
}

beforeEach(function () {
    $this->travelTo('2026-10-01 12:00:00');
});

test('anyone can view a meetup, including guests', function () {
    $group = Chapter::factory()->active()->create(['name' => 'Glasgow Streetwatchers']);
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps(10)->create([
        'title' => 'Sunday walk',
        'timezone' => 'Europe/London',
    ]);

    $this->get(route('chapters.events.show', [$group, $meetup]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('meetups/Show')
            ->where('group.name', 'Glasgow Streetwatchers')
            ->where('meetup.title', 'Sunday walk')
            ->where('meetup.timezone', 'Europe/London')
            ->where('meetup.rsvp_limit', 10)
            ->where('meetup.is_accepting_rsvps', true)
            ->where('viewer.is_guest', true)
            ->where('attendees', null));
});

test('a meetup is only found under its own active group', function () {
    $meetup = Event::factory()->for(Chapter::factory()->active())->upcoming()->create();
    $otherGroup = Chapter::factory()->active()->create();
    $pendingGroup = Chapter::factory()->pending()->create();
    $pendingMeetup = Event::factory()->for($pendingGroup)->upcoming()->create();

    $this->get(route('chapters.events.show', [$otherGroup, $meetup]))->assertNotFound();
    $this->get(route('chapters.events.show', [$pendingGroup, $pendingMeetup]))->assertNotFound();
});

test('an organiser can plan a meetup, entered in its own time zone', function () {
    [$group, $organiser] = groupWithOrganiser();

    $this->actingAs($organiser)
        ->get(route('chapters.events.create', $group))
        ->assertOk();

    $response = $this->actingAs($organiser)
        ->post(route('chapters.events.store', $group), meetupDetails())
        ->assertSessionHasNoErrors();

    $meetup = Event::query()->sole();

    $response->assertRedirect(route('chapters.events.show', [$group, $meetup]))
        ->assertSessionHas('status', 'meetup-created');

    // 10:00 in London during British Summer Time is 09:00 UTC.
    expect($meetup)
        ->chapter_id->toBe($group->id)
        ->organizer_id->toBe($organiser->id)
        ->title->toBe('Sunday walk along the Clyde')
        ->timezone->toBe('Europe/London')
        ->rsvps_enabled->toBeTrue()
        ->rsvp_limit->toBe(12)
        ->and($meetup->starts_at->toDateTimeString())->toBe('2026-10-04 09:00:00')
        ->and($meetup->ends_at->toDateTimeString())->toBe('2026-10-04 11:30:00');
});

test('a meetup without RSVPs has no limit, even if one was entered', function () {
    [$group, $organiser] = groupWithOrganiser();

    $this->actingAs($organiser)
        ->post(route('chapters.events.store', $group), meetupDetails(['rsvps_enabled' => '0', 'rsvp_limit' => '12']))
        ->assertSessionHasNoErrors();

    expect(Event::query()->sole())
        ->rsvps_enabled->toBeFalse()
        ->rsvp_limit->toBeNull();
});

test('only organisers can plan a meetup', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->get(route('chapters.events.create', $group))->assertRedirect(route('login'));

    $this->actingAs($member)->get(route('chapters.events.create', $group))->assertForbidden();
    $this->actingAs($member)->post(route('chapters.events.store', $group), meetupDetails())->assertForbidden();

    expect(Event::query()->count())->toBe(0);
});

test('planning a meetup requires its details', function () {
    [$group, $organiser] = groupWithOrganiser();

    $this->actingAs($organiser)
        ->post(route('chapters.events.store', $group), [])
        ->assertSessionHasErrors([
            'title' => 'The title field is required.',
            'description' => 'The description field is required.',
            'location_name' => 'The location name field is required.',
            'date' => 'The date field is required.',
            'starts_at_time' => 'The starts at time field is required.',
            'ends_at_time' => 'The ends at time field is required.',
            'timezone' => 'The timezone field is required.',
        ]);
});

test('planning a meetup rejects invalid details', function (array $overrides, string $field, string $message) {
    [$group, $organiser] = groupWithOrganiser();

    $this->actingAs($organiser)
        ->post(route('chapters.events.store', $group), meetupDetails($overrides))
        ->assertSessionHasErrors([$field => $message]);

    expect(Event::query()->count())->toBe(0);
})->with([
    'ends before it starts' => [['ends_at_time' => '09:00'], 'ends_at_time', 'The meetup must end after it starts.'],
    'already started' => [['date' => '2026-10-01', 'starts_at_time' => '12:00'], 'date', 'The meetup must start in the future.'],
    'limit of zero' => [['rsvp_limit' => '0'], 'rsvp_limit', 'The limit must be between 1 and 1,000 people.'],
    'unknown time zone' => [['timezone' => 'Mars/Olympus'], 'timezone', 'The timezone field must be a valid timezone.'],
]);

test('the edit form shows the meetup in its own time zone', function () {
    [$group, $organiser] = groupWithOrganiser();
    $meetup = Event::factory()->for($group)->create([
        'starts_at' => '2026-10-04 09:00:00',
        'ends_at' => '2026-10-04 11:30:00',
        'timezone' => 'Europe/London',
    ]);

    $this->actingAs($organiser)
        ->get(route('chapters.events.edit', [$group, $meetup]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('meetups/Edit')
            ->where('meetup.date', '2026-10-04')
            ->where('meetup.starts_at_time', '10:00')
            ->where('meetup.ends_at_time', '12:30'));
});

test('any organiser of the group can edit a meetup', function () {
    [$group, $organiser] = groupWithOrganiser();
    $meetup = Event::factory()->for($group)->upcoming()->create();

    $this->actingAs($organiser)
        ->put(route('chapters.events.update', [$group, $meetup]), meetupDetails(['title' => 'Moved to Saturday']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('chapters.events.show', [$group, $meetup]));

    expect($meetup->fresh()->title)->toBe('Moved to Saturday');
});

test('a member who is not an organiser cannot edit or cancel a meetup', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);
    $meetup = Event::factory()->for($group)->upcoming()->create(['title' => 'Original']);

    $this->actingAs($member)
        ->put(route('chapters.events.update', [$group, $meetup]), meetupDetails())
        ->assertForbidden();
    $this->actingAs($member)
        ->post(route('chapters.events.cancellation.store', [$group, $meetup]))
        ->assertForbidden();

    expect($meetup->fresh())
        ->title->toBe('Original')
        ->cancelled_at->toBeNull();
});

test('an organiser can cancel a meetup and it stays visible, marked as cancelled', function () {
    [$group, $organiser] = groupWithOrganiser();
    $meetup = Event::factory()->for($group)->upcoming()->takingRsvps()->create();

    $this->actingAs($organiser)
        ->post(route('chapters.events.cancellation.store', [$group, $meetup]))
        ->assertRedirect(route('chapters.events.show', [$group, $meetup]))
        ->assertSessionHas('status', 'meetup-cancelled');

    expect($meetup->fresh()->isCancelled())->toBeTrue();

    $this->get(route('chapters.show', $group))
        ->assertInertia(fn (Assert $page) => $page
            ->where('upcomingEvents.0.id', $meetup->id)
            ->where('upcomingEvents.0.is_cancelled', true));
});

test('cancelled and finished meetups cannot be changed', function (Closure $makeMeetup) {
    [$group, $organiser] = groupWithOrganiser();
    $meetup = $makeMeetup($group);

    $this->actingAs($organiser)->get(route('chapters.events.edit', [$group, $meetup]))->assertForbidden();
    $this->actingAs($organiser)->put(route('chapters.events.update', [$group, $meetup]), meetupDetails())->assertForbidden();
    $this->actingAs($organiser)->post(route('chapters.events.cancellation.store', [$group, $meetup]))->assertForbidden();
})->with([
    'cancelled' => [fn (Chapter $group) => Event::factory()->for($group)->upcoming()->cancelled()->create()],
    'finished' => [fn (Chapter $group) => Event::factory()->for($group)->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHours(2),
    ])],
]);

test('organisers of a group that is no longer active cannot plan meetups', function () {
    [$group, $organiser] = groupWithOrganiser();
    $group->update(['status' => ChapterStatus::Inactive]);

    $this->actingAs($organiser)
        ->post(route('chapters.events.store', $group), meetupDetails())
        ->assertForbidden();
});
