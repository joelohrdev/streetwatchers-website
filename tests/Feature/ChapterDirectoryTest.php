<?php

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Enums\Country;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the chapter directory is public and lists only active chapters', function () {
    $active = Chapter::factory()->active()->create([
        'name' => 'Lisbon Streetwatchers',
        'city' => 'Lisbon',
        'country' => Country::Portugal,
        'latitude' => 38.7223,
        'longitude' => -9.1393,
    ]);
    Chapter::factory()->pending()->create();
    Chapter::factory()->create(['status' => ChapterStatus::Inactive]);

    $this->get(route('chapters.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('chapters/Index')
            ->has('chapters', 1)
            ->where('chapters.0.id', $active->id)
            ->where('chapters.0.city', 'Lisbon')
            ->where('chapters.0.country', 'Portugal')
            ->where('chapters.0.latitude', 38.7223)
            ->where('chapters.0.longitude', -9.1393)
            ->where('search', ''));
});

test('the chapter directory starts with the search from the homepage', function () {
    $this->get(route('chapters.index', ['q' => '  Lisbon ']))
        ->assertInertia(fn (Assert $page) => $page->where('search', 'Lisbon'));
});

test('an active chapter has a public page with its organizers, upcoming events and nearest chapters', function () {
    $lisbon = Chapter::factory()->active()->create([
        'name' => 'Lisbon Streetwatchers', 'latitude' => 38.7223, 'longitude' => -9.1393,
    ]);
    $setubal = Chapter::factory()->active()->create(['name' => 'Setúbal Streetwatchers', 'latitude' => 38.5244, 'longitude' => -8.8882]);
    $evora = Chapter::factory()->active()->create(['name' => 'Évora Streetwatchers', 'latitude' => 38.5714, 'longitude' => -7.9135]);
    // Porto is about 170 miles away, beyond the nearby radius.
    Chapter::factory()->active()->create(['name' => 'Porto Streetwatchers', 'latitude' => 41.1579, 'longitude' => -8.6291]);
    Chapter::factory()->pending()->create(['latitude' => 38.7300, 'longitude' => -9.1400]);

    $lisbon->members()->attach(User::factory()->create(['name' => 'Ana Organizer', 'instagram_handle' => 'ana.walks']), ['role' => ChapterMemberRole::Admin]);
    $lisbon->members()->attach(User::factory()->create(['name' => 'Rui Member']), ['role' => ChapterMemberRole::Member]);

    $upcoming = Event::factory()->for($lisbon)->create(['starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(3)->addHours(2)]);
    Event::factory()->for($lisbon)->create(['starts_at' => now()->subWeek(), 'ends_at' => now()->subWeek()->addHours(2)]);

    $this->get(route('chapters.show', $lisbon))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('chapters/Show')
            ->where('chapter.id', $lisbon->id)
            ->where('chapter.members_count', 2)
            ->where('organizers', [['name' => 'Ana Organizer', 'instagram_handle' => 'ana.walks']])
            ->has('upcomingEvents', 1)
            ->where('upcomingEvents.0.id', $upcoming->id)
            ->has('nearby', 2)
            ->where('nearby.0.id', $setubal->id)
            ->where('nearby.0.distance', 31)
            ->where('nearby.1.id', $evora->id)
            ->where('nearbyRadiusMiles', 100));
});

test('a chapter with no other groups within 100 miles suggests none', function () {
    $lisbon = Chapter::factory()->active()->create(['latitude' => 38.7223, 'longitude' => -9.1393]);
    Chapter::factory()->active()->create(['latitude' => 41.1579, 'longitude' => -8.6291]);

    $this->get(route('chapters.show', $lisbon))
        ->assertInertia(fn (Assert $page) => $page->has('nearby', 0));
});

test('chapters that are not active have no public page', function (ChapterStatus $status) {
    $chapter = Chapter::factory()->create(['status' => $status]);

    $this->get(route('chapters.show', $chapter))->assertNotFound();
})->with([ChapterStatus::Pending, ChapterStatus::Inactive]);

test('the start a group page is not mistaken for a group slug', function () {
    $this->actingAs(User::factory()->create())
        ->get('/groups/create')
        ->assertInertia(fn (Assert $page) => $page->component('chapters/Create'));
});

test('chapters are served under /groups on the public site', function () {
    $chapter = Chapter::factory()->active()->create(['slug' => 'lisbon']);

    expect(route('chapters.index', absolute: false))->toBe('/groups')
        ->and(route('chapters.show', $chapter, absolute: false))->toBe('/groups/lisbon')
        ->and(route('chapters.create', absolute: false))->toBe('/groups/create');
});

test('old chapter links redirect permanently to their group URLs', function (string $from, string $to) {
    $this->get($from)->assertRedirect($to)->assertStatus(301);
})->with([
    'directory' => ['/chapters', '/groups'],
    'directory search' => ['/chapters?q=Japan', '/groups?q=Japan'],
    'group page' => ['/chapters/lisbon', '/groups/lisbon'],
    'start a group' => ['/chapters/create', '/groups/create'],
]);
