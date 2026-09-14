<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('every admin route returns 403 for a member', function () {
    $member = User::factory()->create();
    $requests = adminRequests();

    $allowed = collect($requests)
        ->reject(fn (array $request): bool => $this->actingAs($member)->call($request['method'], $request['url'])->isForbidden())
        ->map(fn (array $request): string => "{$request['method']} {$request['name']}");

    expect($requests)->not->toBeEmpty()
        ->and($allowed->all())->toBe([]);
});

test('admin routes return 403 for every role except super admin', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
})->with(collect(UserRole::cases())->reject(fn (UserRole $role): bool => $role === UserRole::SuperAdmin)->all());

test('admin routes redirect guests to the login page', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('the access-admin gate only allows active super admins', function (UserRole $role, UserStatus $status, bool $expected) {
    $user = User::factory()->create(['role' => $role, 'status' => $status]);

    expect(Gate::forUser($user)->allows('access-admin'))->toBe($expected);
})->with([
    'active super admin' => [UserRole::SuperAdmin, UserStatus::Active, true],
    'suspended super admin' => [UserRole::SuperAdmin, UserStatus::Suspended, false],
    'banned super admin' => [UserRole::SuperAdmin, UserStatus::Banned, false],
    'member' => [UserRole::Member, UserStatus::Active, false],
    'chapter admin' => [UserRole::ChapterAdmin, UserStatus::Active, false],
    'correspondent' => [UserRole::Correspondent, UserStatus::Active, false],
]);
