<?php

use App\Enums\AuditAction;
use App\Enums\UserStatus;
use App\Models\User;

test('a user can be suspended with a logged reason', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->put(route('admin.users.status.update', $user), ['status' => 'suspended', 'reason' => 'Repeated harassment.'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->status)->toBe(UserStatus::Suspended);
    $this->assertDatabaseHas('audit_logs', [
        'actor_id' => $admin->id,
        'action' => AuditAction::UserStatusUpdated->value,
        'subject_type' => 'user',
        'subject_id' => $user->id,
        'old_status' => 'active',
        'new_status' => 'suspended',
        'reason' => 'Repeated harassment.',
    ]);
});

test('changing a user status requires a reason', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->put(route('admin.users.status.update', $user), ['status' => 'banned'])
        ->assertSessionHasErrors(['reason' => 'The reason field is required.']);

    expect($user->fresh()->status)->toBe(UserStatus::Active);
});

test('a super admin cannot change the status of their own account', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->put(route('admin.users.status.update', $admin), ['status' => 'banned', 'reason' => 'Oops.'])
        ->assertSessionHasErrors(['status' => 'You cannot change the status of your own account.']);

    expect($admin->fresh()->status)->toBe(UserStatus::Active);
});

test('blocked users cannot log in', function (string $state, string $message) {
    $user = User::factory()->{$state}()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors(['email' => $message]);

    $this->assertGuest();
})->with([
    'suspended' => ['suspended', 'This account has been suspended. Contact support if you believe this is a mistake.'],
    'banned' => ['banned', 'This account has been banned from StreetWatchers.'],
]);

test('a blocked user with the wrong password sees the generic login error', function () {
    $user = User::factory()->banned()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors(['email' => __('auth.failed')]);
});

test('a user who is suspended mid-session is signed out on their next request', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $user->forceFill(['status' => UserStatus::Suspended])->save();

    $this->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => UserStatus::Suspended->loginMessage()]);
    $this->assertGuest();
});
