<?php

use App\Enums\Country;
use App\Models\Chapter;
use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

test('the homepage renders the marketing page for guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Home'));
});

test('the homepage counts active groups and the countries they are in', function () {
    Chapter::factory()->active()->create(['country' => Country::Portugal]);
    Chapter::factory()->active()->create(['country' => Country::Portugal]);
    Chapter::factory()->active()->create(['country' => Country::Spain]);
    Chapter::factory()->pending()->create(['country' => Country::France]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('chapterStats.chapterCount', 3)
            ->where('chapterStats.countryCount', 2));
});

test('the homepage lists upcoming meetups from active groups, soonest first', function () {
    $this->travelTo('2026-10-01 12:00:00');
    $group = Chapter::factory()->active()->create(['name' => 'Glasgow Streetwatchers', 'city' => 'Glasgow', 'country' => Country::UnitedKingdom]);
    $later = Event::factory()->for($group)->create(['title' => 'Later walk', 'starts_at' => '2026-10-10 10:00', 'ends_at' => '2026-10-10 12:00']);
    $sooner = Event::factory()->for($group)->create(['title' => 'Sooner walk', 'starts_at' => '2026-10-03 10:00', 'ends_at' => '2026-10-03 12:00']);

    Event::factory()->for($group)->canceled()->create(['starts_at' => '2026-10-02 10:00', 'ends_at' => '2026-10-02 12:00']);
    Event::factory()->for($group)->create(['starts_at' => '2026-09-30 10:00', 'ends_at' => '2026-09-30 12:00']);
    Event::factory()->for(Chapter::factory()->pending())->create(['starts_at' => '2026-10-02 10:00', 'ends_at' => '2026-10-02 12:00']);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('upcomingMeetups', 2)
            ->where('upcomingMeetups.0.id', $sooner->id)
            ->where('upcomingMeetups.0.group', [
                'name' => 'Glasgow Streetwatchers',
                'slug' => $group->slug,
                'city' => 'Glasgow',
                'country' => 'United Kingdom',
            ])
            ->where('upcomingMeetups.1.id', $later->id));
});

test('the homepage shows at most six upcoming meetups', function () {
    Event::factory()->for(Chapter::factory()->active())->upcoming()->count(7)->create();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->has('upcomingMeetups', 6));
});
