<?php

use App\Enums\ChapterMemberRole;
use App\Models\Chapter;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests viewing a group are told to log in or register to join', function () {
    $group = Chapter::factory()->active()->create();

    $this->get(route('chapters.show', $group))
        ->assertInertia(fn (Assert $page) => $page->where('membership', 'guest'));
});

test('the group page shows how a signed-in user relates to the group', function (?ChapterMemberRole $role, string $membership) {
    $group = Chapter::factory()->active()->create();
    $user = User::factory()->create();

    if ($role !== null) {
        $group->members()->attach($user, ['role' => $role]);
    }

    $this->actingAs($user)
        ->get(route('chapters.show', $group))
        ->assertInertia(fn (Assert $page) => $page->where('membership', $membership));
})->with([
    'not in the group' => [null, 'none'],
    'member' => [ChapterMemberRole::Member, 'member'],
    'organizer' => [ChapterMemberRole::Admin, 'organizer'],
]);

test('a guest who logs in to join is brought back to the group page', function () {
    $group = Chapter::factory()->active()->create();
    $user = User::factory()->create();

    $this->get(route('chapters.membership.create', [$group, 'via' => 'login']))
        ->assertRedirect(route('login'));

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('chapters.show', $group));
});

test('a guest who registers to join is brought back to the group page', function () {
    $group = Chapter::factory()->active()->create();

    $this->get(route('chapters.membership.create', [$group, 'via' => 'register']))
        ->assertRedirect(route('register'));

    $this->post(route('register.store'), [
        'name' => 'New Walker',
        'email' => 'walker@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ])->assertRedirect(route('chapters.show', $group));
});

test('a signed-in user following a join link goes straight to the group page', function () {
    $group = Chapter::factory()->active()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('chapters.membership.create', [$group, 'via' => 'login']))
        ->assertRedirect(route('chapters.show', $group));
});

test('a member can join a group', function () {
    $group = Chapter::factory()->active()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('chapters.membership.store', $group))
        ->assertRedirect(route('chapters.show', $group))
        ->assertSessionHas('status', 'group-joined');

    expect($group->members()->sole())
        ->id->toBe($user->id)
        ->pivot->role->toBe(ChapterMemberRole::Member);
});

test('joining again does not demote an organizer', function () {
    $group = Chapter::factory()->active()->create();
    $organizer = User::factory()->create();
    $group->members()->attach($organizer, ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($organizer)->post(route('chapters.membership.store', $group));

    expect($group->members()->sole()->pivot->role)->toBe(ChapterMemberRole::Admin);
});

test('guests cannot join without logging in', function () {
    $group = Chapter::factory()->active()->create();

    $this->post(route('chapters.membership.store', $group))->assertRedirect(route('login'));

    expect($group->members()->count())->toBe(0);
});

test('only active groups can be joined', function () {
    $group = Chapter::factory()->pending()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('chapters.membership.store', $group))->assertNotFound();
    $this->get(route('chapters.membership.create', $group))->assertNotFound();

    expect($group->members()->count())->toBe(0);
});

test('a member can leave a group', function () {
    $group = Chapter::factory()->active()->create();
    $member = User::factory()->create();
    $group->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->actingAs($member)
        ->delete(route('chapters.membership.destroy', $group))
        ->assertRedirect(route('chapters.show', $group))
        ->assertSessionHas('status', 'group-left');

    expect($group->members()->count())->toBe(0);
});

test('an organizer cannot leave their group from the group page', function () {
    $group = Chapter::factory()->active()->create();
    $organizer = User::factory()->create();
    $group->members()->attach($organizer, ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($organizer)
        ->delete(route('chapters.membership.destroy', $group))
        ->assertSessionHasErrors(['membership' => 'Step down as an organizer from the members page before you leave the group.']);

    expect($group->members()->sole()->id)->toBe($organizer->id);
});
