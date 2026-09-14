<?php

use App\Enums\CollectiveMemberRole;
use App\Models\Collective;
use App\Models\CollectiveApplication;
use App\Models\Photo;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the collective directory is public and lists verified collectives first', function () {
    $newer = Collective::factory()->create(['name' => 'Newer', 'created_at' => now()]);
    $verified = Collective::factory()->verified()->create(['name' => 'Verified', 'created_at' => now()->subYear()]);
    Collective::factory()->create()->delete();

    $this->get(route('collectives.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('collectives/Index')
            ->has('collectives', 2)
            ->where('collectives.0.id', $verified->id)
            ->where('collectives.1.id', $newer->id));
});

test('a collective page lists its founders, members and members\' recent published photos', function () {
    $collective = Collective::factory()->create();
    $founder = User::factory()->create(['name' => 'Ada Founder']);
    $member = User::factory()->create(['name' => 'Ben Member']);
    $collective->members()->attach($founder, ['role' => CollectiveMemberRole::Founder]);
    $collective->members()->attach($member, ['role' => CollectiveMemberRole::Member]);
    $published = Photo::factory()->published()->for($member)->create();
    Photo::factory()->for($member)->create(['status' => 'pending']);
    Photo::factory()->published()->create();

    $this->get(route('collectives.show', $collective))
        ->assertInertia(fn (Assert $page) => $page
            ->component('collectives/Show')
            ->where('collective.members_count', 2)
            ->where('founders', ['Ada Founder'])
            ->where('members', ['Ben Member'])
            ->has('recentPhotos', 1)
            ->where('recentPhotos.0.id', $published->id));
});

test('a removed collective has no public page', function () {
    $collective = Collective::factory()->create();
    $collective->delete();

    $this->get(route('collectives.show', $collective))->assertNotFound();
});

test('the collective page shows how the visitor relates to the collective', function (Closure $arrange, string $viewer) {
    $collective = Collective::factory()->openForApplications()->create();
    $user = User::factory()->create();
    $arrange($collective, $user);

    $this->actingAs($user)
        ->get(route('collectives.show', $collective))
        ->assertInertia(fn (Assert $page) => $page->where('viewer', $viewer));
})->with([
    'can apply' => [fn () => null, 'open'],
    'founder' => [fn (Collective $collective, User $user) => $collective->members()->attach($user, ['role' => CollectiveMemberRole::Founder]), 'founder'],
    'member' => [fn (Collective $collective, User $user) => $collective->members()->attach($user, ['role' => CollectiveMemberRole::Member]), 'member'],
    'pending application' => [fn (Collective $collective, User $user) => CollectiveApplication::factory()->pending()->for($collective)->for($user)->create(), 'pending'],
    'declined before' => [fn (Collective $collective, User $user) => CollectiveApplication::factory()->declined()->for($collective)->for($user)->create(), 'declined'],
    'not taking applications' => [fn (Collective $collective) => $collective->update(['is_open_for_applications' => false]), 'closed'],
]);

test('guests see the guest state on a collective page', function () {
    $this->get(route('collectives.show', Collective::factory()->create()))
        ->assertInertia(fn (Assert $page) => $page->where('viewer', 'guest')->where('pendingApplicationsCount', null));
});
