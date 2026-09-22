<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the privacy policy and code of conduct are public', function (string $route, string $component) {
    $this->get(route($route))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    'privacy policy' => ['privacy', 'policies/Privacy'],
    'code of conduct' => ['code-of-conduct', 'policies/CodeOfConduct'],
]);
