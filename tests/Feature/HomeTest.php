<?php

use App\Models\Chapter;
use Inertia\Testing\AssertableInertia as Assert;

test('the homepage renders the marketing page for guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Home'));
});

test('the homepage counts active groups and the countries they are in', function () {
    Chapter::factory()->active()->create(['country' => 'Portugal']);
    Chapter::factory()->active()->create(['country' => 'Portugal']);
    Chapter::factory()->active()->create(['country' => 'Spain']);
    Chapter::factory()->pending()->create(['country' => 'France']);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('chapterStats.chapterCount', 3)
            ->where('chapterStats.countryCount', 2));
});
