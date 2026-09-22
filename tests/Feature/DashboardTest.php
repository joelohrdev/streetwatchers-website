<?php

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Enums\CollectiveMemberRole;
use App\Enums\EventRsvpStatus;
use App\Models\Chapter;
use App\Models\Collective;
use App\Models\CollectiveApplication;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('your streetwatchers lists the member\'s groups, collectives and applications', function () {
    $user = User::factory()->create();

    $organized = Chapter::factory()->active()->create(['name' => 'Alpha Walkers']);
    $organized->members()->attach($user, ['role' => ChapterMemberRole::Admin]);
    $joined = Chapter::factory()->active()->create(['name' => 'Beta Walkers']);
    $joined->members()->attach($user, ['role' => ChapterMemberRole::Member]);

    $founded = Collective::factory()->create(['name' => 'Alpha Collective']);
    $founded->members()->attach($user, ['role' => CollectiveMemberRole::Founder]);
    CollectiveApplication::factory(2)->pending()->for($founded)->create();
    $member = Collective::factory()->create(['name' => 'Beta Collective']);
    $member->members()->attach($user, ['role' => CollectiveMemberRole::Member]);

    $appliedTo = Collective::factory()->create(['name' => 'Gamma Collective']);
    CollectiveApplication::factory()->pending()->for($appliedTo)->for($user)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('groups.0.name', 'Alpha Walkers')
            ->where('groups.0.role', 'organizer')
            ->where('groups.1.role', 'member')
            ->where('collectives.0.name', 'Alpha Collective')
            ->where('collectives.0.role', 'founder')
            ->where('collectives.0.pending_applications_count', 2)
            ->where('collectives.1.role', 'member')
            ->where('collectives.1.pending_applications_count', null)
            ->has('applications', 1)
            ->where('applications.0.collective.name', 'Gamma Collective')
            ->where('applications.0.status', 'pending'));
});

test('the dashboard lists upcoming meetups from the member\'s groups, marking the ones they\'re going to', function () {
    $this->travelTo('2026-10-01 12:00:00');
    $member = User::factory()->create();
    $group = Chapter::factory()->active()->create(['name' => 'Glasgow Streetwatchers']);
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $going = Event::factory()->for($group)->takingRsvps()->create(['starts_at' => '2026-10-03 10:00', 'ends_at' => '2026-10-03 12:00']);
    $going->rsvps()->attach($member, ['status' => EventRsvpStatus::Going]);
    $notResponded = Event::factory()->for($group)->create(['starts_at' => '2026-10-10 10:00', 'ends_at' => '2026-10-10 12:00']);
    $canceled = Event::factory()->for($group)->canceled()->create(['starts_at' => '2026-10-05 10:00', 'ends_at' => '2026-10-05 12:00']);

    // Not listed: a meetup that's over, one in a group they're not in, and one in a group that's no longer active.
    Event::factory()->for($group)->create(['starts_at' => '2026-09-30 10:00', 'ends_at' => '2026-09-30 12:00']);
    Event::factory()->for(Chapter::factory()->active())->create(['starts_at' => '2026-10-04 10:00', 'ends_at' => '2026-10-04 12:00']);
    $inactiveGroup = Chapter::factory()->create(['status' => ChapterStatus::Inactive]);
    $inactiveGroup->members()->attach($member, ['role' => ChapterMemberRole::Member]);
    Event::factory()->for($inactiveGroup)->create(['starts_at' => '2026-10-04 10:00', 'ends_at' => '2026-10-04 12:00']);

    $this->actingAs($member)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('upcomingMeetups', 3)
            ->where('upcomingMeetups.0.id', $going->id)
            ->where('upcomingMeetups.0.is_going', true)
            ->where('upcomingMeetups.0.group.name', 'Glasgow Streetwatchers')
            ->where('upcomingMeetups.1.id', $canceled->id)
            ->where('upcomingMeetups.1.is_canceled', true)
            ->where('upcomingMeetups.2.id', $notResponded->id)
            ->where('upcomingMeetups.2.is_going', false));
});
