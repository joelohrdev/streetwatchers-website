<?php

use App\Models\Chapter;
use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

test('a short code is the ID in base 62 and finds the same record again', function (int $id, string $code) {
    $group = Chapter::factory()->active()->create(['id' => $id]);

    expect($group->shortCode())->toBe($code)
        ->and(Chapter::findByShortCode($code)?->is($group))->toBeTrue();
})->with([
    'single digit' => [7, '7'],
    'last single character' => [61, 'Z'],
    'first two characters' => [62, '10'],
    'larger ID' => [12345, '3d7'],
]);

test('a group short link redirects to the group page', function () {
    $group = Chapter::factory()->active()->create(['id' => 12345]);

    expect($group->shareUrl())->toBe(url('/g/3d7'));

    $this->get('/g/3d7')->assertRedirect(route('chapters.show', $group))->assertStatus(301);
});

test('a meetup short link redirects to the meetup page', function () {
    $group = Chapter::factory()->active()->create();
    $meetup = Event::factory()->for($group)->upcoming()->create(['id' => 62]);

    expect($meetup->shareUrl())->toBe(url('/m/10'));

    $this->get('/m/10')->assertRedirect(route('chapters.events.show', [$group, $meetup]))->assertStatus(301);
});

test('short links for missing or hidden pages are not found', function (Closure $url) {
    $this->get($url())->assertNotFound();
})->with([
    'no group with that ID' => [fn () => '/g/zz'],
    'too long to be real' => [fn () => '/g/'.str_repeat('Z', 11)],
    'group awaiting approval' => [fn () => Chapter::factory()->pending()->create()->shareUrl()],
    'meetup in a group awaiting approval' => [fn () => Event::factory()->for(Chapter::factory()->pending())->create()->shareUrl()],
]);

test('group and meetup pages get their short links to share', function () {
    $group = Chapter::factory()->active()->create();
    $meetup = Event::factory()->for($group)->upcoming()->create();

    $this->get(route('chapters.show', $group))
        ->assertInertia(fn (Assert $page) => $page->where('chapter.share_url', $group->shareUrl()));
    $this->get(route('chapters.events.show', [$group, $meetup]))
        ->assertInertia(fn (Assert $page) => $page->where('meetup.share_url', $meetup->shareUrl()));
});
