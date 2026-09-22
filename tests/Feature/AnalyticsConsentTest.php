<?php

use Inertia\Testing\AssertableInertia as Assert;

test('analytics is off when no Google Analytics ID is set', function () {
    config(['services.google_analytics.measurement_id' => null]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('analytics', null));
});

test('pages get the Google Analytics ID to ask visitors for consent', function () {
    config(['services.google_analytics.measurement_id' => 'G-TEST123']);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('analytics', ['measurementId' => 'G-TEST123']));
});

test('Google Analytics never loads from the server-rendered page, only after consent in the browser', function () {
    config(['services.google_analytics.measurement_id' => 'G-TEST123']);

    $this->get(route('home'))->assertDontSee('googletagmanager.com', false);
});
