<?php

use App\Models\Chapter;
use App\Models\Event;

test('pages have default link preview tags', function () {
    $this->get(route('home'))
        ->assertSee('<meta property="og:title" content="StreetWatchers">', false)
        ->assertSee('<meta property="og:image" content="'.asset('og-image.jpg').'">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

test('a group page previews with its name and description', function () {
    $group = Chapter::factory()->active()->create([
        'name' => 'Glasgow Streetwatchers',
        'description' => "Weekly walks along the Clyde.\n\nAll welcome.",
    ]);

    $this->get(route('chapters.show', $group))
        ->assertSee('<meta property="og:title" content="Glasgow Streetwatchers">', false)
        ->assertSee('<meta property="og:description" content="Weekly walks along the Clyde. All welcome.">', false)
        ->assertSee('<meta property="og:url" content="'.route('chapters.show', $group).'">', false);
});

test('a meetup page previews with when and where it is, in its own time zone', function () {
    $this->travelTo('2026-10-01 12:00:00');
    $group = Chapter::factory()->active()->create(['name' => 'Glasgow Streetwatchers']);
    $meetup = Event::factory()->for($group)->create([
        'title' => 'Sunday walk',
        'description' => 'West along the river.',
        'location_name' => 'Riverside Museum',
        'starts_at' => '2026-10-04 09:00:00',
        'ends_at' => '2026-10-04 11:00:00',
        'timezone' => 'Europe/London',
    ]);

    $this->get(route('chapters.events.show', [$group, $meetup]))
        ->assertSee('<meta property="og:title" content="Sunday walk">', false)
        ->assertSee('<meta property="og:description" content="Sun, Oct 4, 10:00 AM BST at Riverside Museum, with Glasgow Streetwatchers. West along the river.">', false);
});

test('a canceled meetup says so in its preview', function () {
    $group = Chapter::factory()->active()->create();
    $meetup = Event::factory()->for($group)->upcoming()->canceled()->create(['title' => 'Sunday walk']);

    $this->get(route('chapters.events.show', [$group, $meetup]))
        ->assertSee('<meta property="og:title" content="Canceled: Sunday walk">', false);
});

test('member-written text is escaped in preview tags', function () {
    $group = Chapter::factory()->active()->create(['name' => 'Quote" onload="alert(1)']);

    $this->get(route('chapters.show', $group))
        ->assertSee('content="Quote&quot; onload=&quot;alert(1)"', false)
        ->assertDontSee('content="Quote" onload="alert(1)"', false);
});

/**
 * The og:description a group page gives for the description.
 */
function previewDescriptionFor(string $description): string
{
    $group = Chapter::factory()->active()->create(['description' => $description]);

    preg_match('/<meta property="og:description" content="([^"]*)">/', test()->get(route('chapters.show', $group))->getContent(), $matches);

    return html_entity_decode($matches[1], ENT_QUOTES);
}

test('a long description ends at the last full sentence that fits', function () {
    $description = 'We\'re street photographers in and around the Chicago area who meet up to walk the city with our cameras. '
        .'Walks are free and open to everyone, whatever you shoot with, phones included. '
        .'We pick a neighborhood, spread out, shoot for a couple of hours, then regroup.';

    expect(previewDescriptionFor($description))->toBe(
        'We\'re street photographers in and around the Chicago area who meet up to walk the city with our cameras. '
        .'Walks are free and open to everyone, whatever you shoot with, phones included.'
    );
});

test('a long description with no sentence ending late enough is cut at a whole word', function () {
    // The only sentence ends near the start, so stopping there would leave an almost empty preview.
    $description = 'Hi there. '.str_repeat('we walk the river and the markets and the alleys ', 6);

    $preview = previewDescriptionFor($description);
    $shortened = mb_substr($preview, 0, -1);

    expect($preview)->toEndWith('…')
        ->and(mb_strlen($shortened))->toBeLessThanOrEqual(200)
        ->and(str_starts_with($description, $shortened.' '))->toBeTrue();
});

test('a short description is used as it is', function () {
    expect(previewDescriptionFor('Weekly walks along the Clyde.'))->toBe('Weekly walks along the Clyde.');
});
