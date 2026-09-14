<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the homepage renders the marketing page for guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Home'));
});
