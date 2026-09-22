<?php

use App\Enums\ChapterMemberRole;
use App\Enums\CollectiveMemberRole;
use App\Models\Chapter;
use App\Models\Collective;
use App\Models\CollectiveApplication;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('your streetwatchers lists the member\'s groups, collectives and applications', function () {
    $user = User::factory()->create();

    $organized = Chapter::factory()->active()->create(['name' => 'Alpha Walkers']);
    $organized->members()->attach($user, ['role' => ChapterMemberRole::Admin]);
    $joined = Chapter::factory()->active()->create(['name' => 'Beta Walkers']);
    $joined->members()->attach($user, ['role' => ChapterMemberRole::Member]);

    $founded = Collective::factory()->create(['name' => 'Alpha Collective']);
    $founded->members()->attach($user, ['role' => CollectiveMemberRole::Founder]);
    CollectiveApplication::factory(2)->pending()->for($founded)->create();
    $member = Collective::factory()->create(['name' => 'Beta Collective']);
    $member->members()->attach($user, ['role' => CollectiveMemberRole::Member]);

    $appliedTo = Collective::factory()->create(['name' => 'Gamma Collective']);
    CollectiveApplication::factory()->pending()->for($appliedTo)->for($user)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('groups.0.name', 'Alpha Walkers')
            ->where('groups.0.role', 'organizer')
            ->where('groups.1.role', 'member')
            ->where('collectives.0.name', 'Alpha Collective')
            ->where('collectives.0.role', 'founder')
            ->where('collectives.0.pending_applications_count', 2)
            ->where('collectives.1.role', 'member')
            ->where('collectives.1.pending_applications_count', null)
            ->has('applications', 1)
            ->where('applications.0.collective.name', 'Gamma Collective')
            ->where('applications.0.status', 'pending'));
});
