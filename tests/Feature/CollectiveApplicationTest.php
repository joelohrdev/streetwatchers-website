<?php

use App\Enums\CollectiveApplicationStatus;
use App\Enums\CollectiveMemberRole;
use App\Models\Collective;
use App\Models\CollectiveApplication;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function collectiveFoundedBy(User $founder, array $attributes = []): Collective
{
    $collective = Collective::factory()->openForApplications()->create($attributes);
    $collective->members()->attach($founder, ['role' => CollectiveMemberRole::Founder]);

    return $collective;
}

test('a guest who logs in to apply is brought back to the collective page', function () {
    $collective = Collective::factory()->openForApplications()->create();
    $user = User::factory()->create();

    $this->get(route('collectives.applications.create', [$collective, 'via' => 'login']))
        ->assertRedirect(route('login'));

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('collectives.show', $collective));
});

test('a guest who registers to apply is brought back to the collective page', function () {
    $collective = Collective::factory()->openForApplications()->create();

    $this->get(route('collectives.applications.create', [$collective, 'via' => 'register']))
        ->assertRedirect(route('register'));

    $this->post(route('register.store'), [
        'name' => 'New Walker',
        'email' => 'walker@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('collectives.show', $collective));
});

test('a member can apply to join an open collective', function () {
    $collective = Collective::factory()->openForApplications()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('collectives.applications.store', $collective), ['message' => 'I photograph night markets and would love to join you.'])
        ->assertRedirect(route('collectives.show', $collective))
        ->assertSessionHas('status', 'application-sent');

    expect(CollectiveApplication::query()->sole())
        ->collective_id->toBe($collective->id)
        ->user_id->toBe($user->id)
        ->status->toBe(CollectiveApplicationStatus::Pending);
});

test('guests cannot apply without logging in', function () {
    $collective = Collective::factory()->openForApplications()->create();

    $this->post(route('collectives.applications.store', $collective), ['message' => str_repeat('a', 30)])
        ->assertRedirect(route('login'));

    expect(CollectiveApplication::query()->count())->toBe(0);
});

test('an application is rejected when the member cannot apply', function (Closure $arrange, string $message) {
    $collective = Collective::factory()->openForApplications()->create();
    $user = User::factory()->create();
    $arrange($collective, $user);

    $this->actingAs($user)
        ->post(route('collectives.applications.store', $collective), ['message' => 'I would really like to join this collective.'])
        ->assertSessionHasErrors(['message' => $message]);
})->with([
    'not taking applications' => [
        fn (Collective $collective) => $collective->update(['is_open_for_applications' => false]),
        'This collective is not taking applications right now.',
    ],
    'already a member' => [
        fn (Collective $collective, User $user) => $collective->members()->attach($user, ['role' => CollectiveMemberRole::Member]),
        'You are already in this collective.',
    ],
    'already applied' => [
        fn (Collective $collective, User $user) => CollectiveApplication::factory()->pending()->for($collective)->for($user)->create(),
        'You have already applied. The founders will get back to you.',
    ],
]);

test('an application needs a short message', function () {
    $collective = Collective::factory()->openForApplications()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('collectives.applications.store', $collective), ['message' => 'Hi'])
        ->assertSessionHasErrors(['message' => 'Tell the founders a little more about yourself.']);
});

test('a member whose application was declined can apply again', function () {
    $collective = Collective::factory()->openForApplications()->create();
    $user = User::factory()->create();
    CollectiveApplication::factory()->declined()->for($collective)->for($user)->create();

    $this->actingAs($user)
        ->post(route('collectives.applications.store', $collective), ['message' => 'I have been shooting a lot more since I last applied.'])
        ->assertSessionHasNoErrors();

    expect($collective->applications()->where('status', 'pending')->count())->toBe(1);
});

test('founders see pending applications only for the collectives they founded', function () {
    $founder = User::factory()->create();
    $mine = collectiveFoundedBy($founder, ['name' => 'Mine']);
    $theirs = collectiveFoundedBy(User::factory()->create(), ['name' => 'Theirs']);
    $application = CollectiveApplication::factory()->pending()->for($mine)->create();
    CollectiveApplication::factory()->pending()->for($theirs)->create();

    $this->actingAs($founder)
        ->get(route('collective-applications.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('collectives/applications/Index')
            ->has('collectives', 1)
            ->where('collectives.0.id', $mine->id)
            ->has('collectives.0.pending', 1)
            ->where('collectives.0.pending.0.id', $application->id));
});

test('the pending application count is shared with founders only', function () {
    $founder = User::factory()->create();
    CollectiveApplication::factory(2)->pending()->for(collectiveFoundedBy($founder))->create();

    $this->actingAs($founder)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('pendingCollectiveApplications', 2));

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('pendingCollectiveApplications', null));
});

test('a founder can accept an application, which adds the applicant as a member', function () {
    $founder = User::factory()->create();
    $collective = collectiveFoundedBy($founder);
    $application = CollectiveApplication::factory()->pending()->for($collective)->create();

    $this->actingAs($founder)
        ->patch(route('collective-applications.decision.update', $application), ['decision' => 'accepted'])
        ->assertSessionHasNoErrors();

    expect($application->fresh())
        ->status->toBe(CollectiveApplicationStatus::Accepted)
        ->decided_by->toBe($founder->id)
        ->decided_at->not->toBeNull()
        ->and($collective->roleOf($application->user))->toBe(CollectiveMemberRole::Member);
});

test('a founder can decline an application without adding the applicant', function () {
    $founder = User::factory()->create();
    $collective = collectiveFoundedBy($founder);
    $application = CollectiveApplication::factory()->pending()->for($collective)->create();

    $this->actingAs($founder)
        ->patch(route('collective-applications.decision.update', $application), ['decision' => 'declined'])
        ->assertSessionHasNoErrors();

    expect($application->fresh()->status)->toBe(CollectiveApplicationStatus::Declined)
        ->and($collective->roleOf($application->user))->toBeNull();
});

test('only founders of the collective can decide an application', function () {
    $collective = collectiveFoundedBy(User::factory()->create());
    $member = User::factory()->create();
    $collective->members()->attach($member, ['role' => CollectiveMemberRole::Member]);
    $application = CollectiveApplication::factory()->pending()->for($collective)->create();

    $this->actingAs($member)
        ->patch(route('collective-applications.decision.update', $application), ['decision' => 'accepted'])
        ->assertForbidden();

    expect($application->fresh()->status)->toBe(CollectiveApplicationStatus::Pending);
});

test('an application that has already been decided cannot be decided again', function () {
    $founder = User::factory()->create();
    $application = CollectiveApplication::factory()->declined()->for(collectiveFoundedBy($founder))->create();

    $this->actingAs($founder)
        ->patch(route('collective-applications.decision.update', $application), ['decision' => 'accepted'])
        ->assertSessionHasErrors(['decision' => 'This application has already been decided.']);
});
