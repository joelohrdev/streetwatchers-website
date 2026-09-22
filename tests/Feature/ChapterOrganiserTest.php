<?php

use App\Enums\AuditAction;
use App\Enums\ChapterMemberRole;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function roleIn(Chapter $group, User $user): ?ChapterMemberRole
{
    return $group->members()->whereKey($user->id)->first()?->pivot->role;
}

test('an organiser sees the group members, organisers first', function () {
    $group = Chapter::factory()->active()->create();
    $organiser = User::factory()->create(['name' => 'Zoe Organiser']);
    $group->members()->attach($organiser, ['role' => ChapterMemberRole::Admin]);
    $group->members()->attach(User::factory()->create(['name' => 'Ana Member']), ['role' => ChapterMemberRole::Member]);

    $this->actingAs($organiser)
        ->get(route('chapters.members.index', $group))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('chapters/Members')
            ->has('members', 2)
            ->where('members.0.name', 'Zoe Organiser')
            ->where('members.0.is_organiser', true)
            ->where('members.0.is_you', true)
            ->where('members.1.name', 'Ana Member')
            ->where('members.1.is_organiser', false));
});

test('only organisers can see the member list', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->get(route('chapters.members.index', $group))->assertRedirect(route('login'));
    $this->actingAs($member)->get(route('chapters.members.index', $group))->assertForbidden();
});

test('an organiser can make a member an organiser', function () {
    $group = Chapter::factory()->active()->create();
    $organiser = User::factory()->create();
    $member = User::factory()->create();
    $group->members()->attach($organiser, ['role' => ChapterMemberRole::Admin]);
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->actingAs($organiser)
        ->post(route('chapters.organisers.store', $group), ['user_id' => $member->id])
        ->assertSessionHasNoErrors();

    expect(roleIn($group, $member))->toBe(ChapterMemberRole::Admin);

    $log = AuditLog::query()->sole();
    expect($log->action)->toBe(AuditAction::ChapterAdminAdded)
        ->and($log->actor_id)->toBe($organiser->id);
});

test('only members of the group can be made organisers', function () {
    $group = Chapter::factory()->active()->create();
    $organiser = User::factory()->create();
    $group->members()->attach($organiser, ['role' => ChapterMemberRole::Admin]);
    $outsider = User::factory()->create();

    $this->actingAs($organiser)
        ->post(route('chapters.organisers.store', $group), ['user_id' => $outsider->id])
        ->assertSessionHasErrors(['user_id' => 'Only members of the group can become organisers.']);

    expect(roleIn($group, $outsider))->toBeNull();
});

test('a member cannot make themselves or anyone else an organiser', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->actingAs($member)
        ->post(route('chapters.organisers.store', $group), ['user_id' => $member->id])
        ->assertForbidden();

    expect(roleIn($group, $member))->toBe(ChapterMemberRole::Member);
});

test('an organiser can step down when another organiser remains', function () {
    $group = Chapter::factory()->active()->create();
    $leaving = User::factory()->create();
    $staying = User::factory()->create();
    $group->members()->attach($leaving, ['role' => ChapterMemberRole::Admin]);
    $group->members()->attach($staying, ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($leaving)
        ->delete(route('chapters.organisers.destroy', $group))
        ->assertRedirect(route('chapters.show', $group))
        ->assertSessionHas('status', 'organiser-stepped-down');

    expect(roleIn($group, $leaving))->toBe(ChapterMemberRole::Member)
        ->and(roleIn($group, $staying))->toBe(ChapterMemberRole::Admin)
        ->and(AuditLog::query()->sole()->action)->toBe(AuditAction::ChapterAdminRemoved);
});

test('the last organiser cannot step down', function () {
    $group = Chapter::factory()->active()->create();
    $organiser = User::factory()->create();
    $group->members()->attach($organiser, ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($organiser)
        ->delete(route('chapters.organisers.destroy', $group))
        ->assertSessionHasErrors(['organiser' => 'Make someone else an organiser before you step down, so the group isn’t left without one.']);

    expect(roleIn($group, $organiser))->toBe(ChapterMemberRole::Admin);
});

test('only organisers of an active group can organise it', function (string $status, ?ChapterMemberRole $role, bool $canOrganise) {
    $group = Chapter::factory()->create(['status' => $status]);
    $user = User::factory()->create();

    if ($role !== null) {
        $group->members()->attach($user, ['role' => $role]);
    }

    expect($user->can('organise', $group))->toBe($canOrganise);
})->with([
    'organiser of an active group' => ['active', ChapterMemberRole::Admin, true],
    'member of an active group' => ['active', ChapterMemberRole::Member, false],
    'not in an active group' => ['active', null, false],
    'organiser of a pending group' => ['pending', ChapterMemberRole::Admin, false],
    'organiser of an inactive group' => ['inactive', ChapterMemberRole::Admin, false],
]);
