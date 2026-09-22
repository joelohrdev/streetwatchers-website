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

test('an organizer sees the group members, organizers first', function () {
    $group = Chapter::factory()->active()->create();
    $organizer = User::factory()->create(['name' => 'Zoe Organizer']);
    $group->members()->attach($organizer, ['role' => ChapterMemberRole::Admin]);
    $group->members()->attach(User::factory()->create(['name' => 'Ana Member']), ['role' => ChapterMemberRole::Member]);

    $this->actingAs($organizer)
        ->get(route('chapters.members.index', $group))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('chapters/Members')
            ->has('members', 2)
            ->where('members.0.name', 'Zoe Organizer')
            ->where('members.0.is_organizer', true)
            ->where('members.0.is_you', true)
            ->where('members.1.name', 'Ana Member')
            ->where('members.1.is_organizer', false));
});

test('only organizers can see the member list', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->get(route('chapters.members.index', $group))->assertRedirect(route('login'));
    $this->actingAs($member)->get(route('chapters.members.index', $group))->assertForbidden();
});

test('an organizer can make a member an organizer', function () {
    $group = Chapter::factory()->active()->create();
    $organizer = User::factory()->create();
    $member = User::factory()->create();
    $group->members()->attach($organizer, ['role' => ChapterMemberRole::Admin]);
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->actingAs($organizer)
        ->post(route('chapters.organizers.store', $group), ['user_id' => $member->id])
        ->assertSessionHasNoErrors();

    expect(roleIn($group, $member))->toBe(ChapterMemberRole::Admin);

    $log = AuditLog::query()->sole();
    expect($log->action)->toBe(AuditAction::ChapterAdminAdded)
        ->and($log->actor_id)->toBe($organizer->id);
});

test('only members of the group can be made organizers', function () {
    $group = Chapter::factory()->active()->create();
    $organizer = User::factory()->create();
    $group->members()->attach($organizer, ['role' => ChapterMemberRole::Admin]);
    $outsider = User::factory()->create();

    $this->actingAs($organizer)
        ->post(route('chapters.organizers.store', $group), ['user_id' => $outsider->id])
        ->assertSessionHasErrors(['user_id' => 'Only members of the group can become organizers.']);

    expect(roleIn($group, $outsider))->toBeNull();
});

test('a member cannot make themselves or anyone else an organizer', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->actingAs($member)
        ->post(route('chapters.organizers.store', $group), ['user_id' => $member->id])
        ->assertForbidden();

    expect(roleIn($group, $member))->toBe(ChapterMemberRole::Member);
});

test('an organizer can step down when another organizer remains', function () {
    $group = Chapter::factory()->active()->create();
    $leaving = User::factory()->create();
    $staying = User::factory()->create();
    $group->members()->attach($leaving, ['role' => ChapterMemberRole::Admin]);
    $group->members()->attach($staying, ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($leaving)
        ->delete(route('chapters.organizers.destroy', $group))
        ->assertRedirect(route('chapters.show', $group))
        ->assertSessionHas('status', 'organizer-stepped-down');

    expect(roleIn($group, $leaving))->toBe(ChapterMemberRole::Member)
        ->and(roleIn($group, $staying))->toBe(ChapterMemberRole::Admin)
        ->and(AuditLog::query()->sole()->action)->toBe(AuditAction::ChapterAdminRemoved);
});

test('the last organizer cannot step down', function () {
    $group = Chapter::factory()->active()->create();
    $organizer = User::factory()->create();
    $group->members()->attach($organizer, ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($organizer)
        ->delete(route('chapters.organizers.destroy', $group))
        ->assertSessionHasErrors(['organizer' => 'Make someone else an organizer before you step down, so the group isn’t left without one.']);

    expect(roleIn($group, $organizer))->toBe(ChapterMemberRole::Admin);
});

test('only organizers of an active group can organize it', function (string $status, ?ChapterMemberRole $role, bool $canOrganize) {
    $group = Chapter::factory()->create(['status' => $status]);
    $user = User::factory()->create();

    if ($role !== null) {
        $group->members()->attach($user, ['role' => $role]);
    }

    expect($user->can('organize', $group))->toBe($canOrganize);
})->with([
    'organizer of an active group' => ['active', ChapterMemberRole::Admin, true],
    'member of an active group' => ['active', ChapterMemberRole::Member, false],
    'not in an active group' => ['active', null, false],
    'organizer of a pending group' => ['pending', ChapterMemberRole::Admin, false],
    'organizer of an inactive group' => ['inactive', ChapterMemberRole::Admin, false],
]);
