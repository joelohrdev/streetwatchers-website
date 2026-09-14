<?php

use App\Enums\AuditAction;
use App\Enums\CritiqueGroupStatus;
use App\Models\CritiqueGroup;
use App\Models\User;

test('a critique group can be dissolved', function () {
    $admin = User::factory()->superAdmin()->create();
    $group = CritiqueGroup::factory()->forming()->create();

    $this->actingAs($admin)
        ->post(route('admin.critique-groups.dissolution.store', $group))
        ->assertSessionHasNoErrors();

    expect($group->fresh()->status)->toBe(CritiqueGroupStatus::Closed);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::CritiqueGroupDissolved->value,
        'subject_type' => 'critique_group',
        'subject_id' => $group->id,
        'old_status' => 'forming',
        'new_status' => 'closed',
    ]);
});

test('a closed critique group cannot be dissolved again', function () {
    $admin = User::factory()->superAdmin()->create();
    $group = CritiqueGroup::factory()->closed()->create();

    $this->actingAs($admin)
        ->post(route('admin.critique-groups.dissolution.store', $group))
        ->assertSessionHasErrors(['critique_group' => 'This group is already closed.']);
});

test('a member can be removed from a critique group', function () {
    $admin = User::factory()->superAdmin()->create();
    $group = CritiqueGroup::factory()->forming()->create();
    $member = User::factory()->create();
    $group->members()->attach($member);

    $this->actingAs($admin)
        ->delete(route('admin.critique-groups.members.destroy', [$group, $member]))
        ->assertSessionHasNoErrors();

    expect($group->members()->count())->toBe(0);
    $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::CritiqueGroupMemberRemoved->value]);
});

test('removing a user who is not in the group returns 404', function () {
    $admin = User::factory()->superAdmin()->create();
    $group = CritiqueGroup::factory()->forming()->create();

    $this->actingAs($admin)
        ->delete(route('admin.critique-groups.members.destroy', [$group, User::factory()->create()]))
        ->assertNotFound();
});

test('a member can be moved to another critique group', function () {
    $admin = User::factory()->superAdmin()->create();
    $from = CritiqueGroup::factory()->forming()->create();
    $to = CritiqueGroup::factory()->forming()->create();
    $member = User::factory()->create();
    $from->members()->attach($member);

    $this->actingAs($admin)
        ->put(route('admin.critique-groups.members.update', [$from, $member]), ['target_group_id' => $to->id])
        ->assertSessionHasNoErrors();

    expect($from->members()->count())->toBe(0)
        ->and($to->members()->sole()->id)->toBe($member->id);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::CritiqueGroupMemberMoved->value,
        'subject_id' => $to->id,
    ]);
});

test('a member cannot be moved into a group that cannot take them', function (Closure $makeTarget, string $message) {
    $admin = User::factory()->superAdmin()->create();
    $from = CritiqueGroup::factory()->forming()->create();
    $member = User::factory()->create();
    $from->members()->attach($member);
    $target = $makeTarget($from, $member);

    $this->actingAs($admin)
        ->put(route('admin.critique-groups.members.update', [$from, $member]), ['target_group_id' => $target->id])
        ->assertSessionHasErrors(['target_group_id' => $message]);

    expect($from->members()->count())->toBe(1);
})->with([
    'closed group' => [
        fn () => CritiqueGroup::factory()->closed()->create(),
        'That group is closed.',
    ],
    'full group' => [
        function () {
            $group = CritiqueGroup::factory()->forming()->create(['max_members' => 2]);
            $group->members()->attach(User::factory(2)->create());

            return $group;
        },
        'That group is full.',
    ],
    'the same group' => [
        fn (CritiqueGroup $from) => $from,
        'Choose a different group.',
    ],
    'a group the user is already in' => [
        function (CritiqueGroup $from, User $member) {
            $group = CritiqueGroup::factory()->forming()->create();
            $group->members()->attach($member);

            return $group;
        },
        'This user is already in that group.',
    ],
]);
