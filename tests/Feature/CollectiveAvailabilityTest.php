<?php

use App\Models\Collective;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('collective pages are not found while collectives are switched off', function (string $method, Closure $url) {
    config(['features.collectives' => false]);
    $collective = Collective::factory()->openForApplications()->create();

    $this->actingAs(User::factory()->create())
        ->call($method, $url($collective))
        ->assertNotFound();
})->with([
    'directory' => ['GET', fn () => route('collectives.index')],
    'collective page' => ['GET', fn (Collective $collective) => route('collectives.show', $collective)],
    'start a collective' => ['GET', fn () => route('collectives.create')],
    'create a collective' => ['POST', fn () => route('collectives.store')],
    'apply' => ['POST', fn (Collective $collective) => route('collectives.applications.store', $collective)],
    'applications inbox' => ['GET', fn () => route('collective-applications.index')],
]);

test('pages are told collectives are switched off, and no application count is sent', function () {
    config(['features.collectives' => false]);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('features.collectives', false)
            ->where('pendingCollectiveApplications', null));
});

test('pages are told when collectives are switched on', function () {
    config(['features.collectives' => true]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('features.collectives', true));
});

test('site admins can still manage collectives while they are switched off', function () {
    config(['features.collectives' => false]);

    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('admin.collectives.index'))
        ->assertOk();
});
