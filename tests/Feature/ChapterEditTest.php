<?php

use App\Enums\ChapterMemberRole;
use App\Enums\Country;
use App\Models\Chapter;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function groupChanges(array $overrides = []): array
{
    return [
        'name' => 'Glasgow Street Photographers',
        'description' => 'Walks along the Clyde every other Sunday.',
        'city' => 'Glasgow',
        'country' => 'GB',
        'latitude' => '55.86',
        'longitude' => '-4.25',
        ...$overrides,
    ];
}

function groupWithItsOrganizer(): array
{
    $group = Chapter::factory()->active()->create([
        'name' => 'Glasgow Streetwatchers',
        'slug' => 'glasgow',
        'latitude' => 55.86,
        'longitude' => -4.25,
    ]);
    $organizer = User::factory()->create();
    $group->members()->attach($organizer, ['role' => ChapterMemberRole::Admin]);

    return [$group, $organizer];
}

test('an organizer can open the edit form with the group details filled in', function () {
    [$group, $organizer] = groupWithItsOrganizer();

    $this->actingAs($organizer)
        ->get(route('chapters.edit', $group))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('chapters/Edit')
            ->where('chapter.name', 'Glasgow Streetwatchers')
            ->where('chapter.country', $group->country->value)
            ->has('countries'));
});

test('an organizer can change the group details and its address stays the same', function () {
    [$group, $organizer] = groupWithItsOrganizer();

    $this->actingAs($organizer)
        ->put(route('chapters.update', $group), groupChanges())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('chapters.show', 'glasgow'))
        ->assertSessionHas('status', 'group-updated');

    expect($group->fresh())
        ->name->toBe('Glasgow Street Photographers')
        ->description->toBe('Walks along the Clyde every other Sunday.')
        ->country->toBe(Country::UnitedKingdom)
        ->slug->toBe('glasgow');
});

test('only organizers can edit a group', function () {
    [$group] = groupWithItsOrganizer();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->get(route('chapters.edit', $group))->assertRedirect(route('login'));
    $this->actingAs($member)->get(route('chapters.edit', $group))->assertForbidden();
    $this->actingAs($member)->put(route('chapters.update', $group), groupChanges())->assertForbidden();

    expect($group->fresh()->name)->toBe('Glasgow Streetwatchers');
});

test('a group can keep its own name but not take another group\'s', function () {
    [$group, $organizer] = groupWithItsOrganizer();
    Chapter::factory()->active()->create(['name' => 'Edinburgh Streetwatchers', 'latitude' => 55.95, 'longitude' => -3.19]);

    $this->actingAs($organizer)
        ->put(route('chapters.update', $group), groupChanges(['name' => 'Glasgow Streetwatchers']))
        ->assertSessionHasNoErrors();

    $this->actingAs($organizer)
        ->put(route('chapters.update', $group), groupChanges(['name' => 'Edinburgh Streetwatchers']))
        ->assertSessionHasErrors(['name' => 'A group with this name already exists.']);
});

test('a group cannot move within the group radius of another group', function () {
    [$group, $organizer] = groupWithItsOrganizer();
    // Paisley is about 7 miles from Glasgow's city center.
    Chapter::factory()->active()->create(['name' => 'Paisley Streetwatchers', 'latitude' => 55.8456, 'longitude' => -4.4239]);

    $this->actingAs($organizer)
        ->put(route('chapters.update', $group), groupChanges(['latitude' => '55.85', 'longitude' => '-4.30']))
        ->assertSessionHasErrors(['latitude' => 'Paisley Streetwatchers is 5 miles from here. Groups must be at least 25 miles apart.']);
});

test('a group already close to another can still update its details if it stays put', function () {
    [$group, $organizer] = groupWithItsOrganizer();
    Chapter::factory()->active()->create(['latitude' => 55.8456, 'longitude' => -4.4239]);

    $this->actingAs($organizer)
        ->put(route('chapters.update', $group), groupChanges(['description' => 'New description.']))
        ->assertSessionHasNoErrors();

    expect($group->fresh()->description)->toBe('New description.');
});
