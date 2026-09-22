<?php

use Inertia\Testing\AssertableInertia as Assert;

test('there are no social links until their URLs are set', function () {
    config(['services.social' => ['instagram' => null, 'facebook' => null]]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('socialLinks', []));
});

test('only the social links with a URL are shared with pages', function () {
    config(['services.social' => [
        'instagram' => 'https://www.instagram.com/streetwatchers/',
        'facebook' => null,
    ]]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('socialLinks', [
            ['label' => 'Instagram', 'url' => 'https://www.instagram.com/streetwatchers/'],
        ]));
});
