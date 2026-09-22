<?php

use App\Models\Chapter;
use App\Models\Event;

/**
 * The paths of the addresses listed in the sitemap, such as "/groups/glasgow".
 *
 * @return list<string>
 */
function sitemapUrls(): array
{
    $response = test()->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = simplexml_load_string($response->getContent());

    return collect(iterator_to_array($xml->url, false))
        ->map(fn ($url) => parse_url((string) $url->loc, PHP_URL_PATH) ?? '/')
        ->all();
}

test('the sitemap lists the public pages, active groups and upcoming meetups', function () {
    $group = Chapter::factory()->active()->create();
    $upcoming = Event::factory()->for($group)->upcoming()->create();

    expect(sitemapUrls())->toContain(
        route('home', absolute: false),
        route('chapters.index', absolute: false),
        route('privacy', absolute: false),
        route('code-of-conduct', absolute: false),
        route('chapters.show', $group, absolute: false),
        route('chapters.events.show', [$group, $upcoming], absolute: false),
    );
});

test('the sitemap leaves out groups that are not live and meetups that are canceled or over', function () {
    $pending = Chapter::factory()->pending()->create();
    $group = Chapter::factory()->active()->create();
    $canceled = Event::factory()->for($group)->upcoming()->canceled()->create();
    $finished = Event::factory()->for($group)->create(['starts_at' => now()->subDay(), 'ends_at' => now()->subDay()->addHours(2)]);
    $inPendingGroup = Event::factory()->for($pending)->upcoming()->create();

    expect(sitemapUrls())->not->toContain(
        route('chapters.show', $pending, absolute: false),
        route('chapters.events.show', [$group, $canceled], absolute: false),
        route('chapters.events.show', [$group, $finished], absolute: false),
        route('chapters.events.show', [$pending, $inPendingGroup], absolute: false),
    );
});
