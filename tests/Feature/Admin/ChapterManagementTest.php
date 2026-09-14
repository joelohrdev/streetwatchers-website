<?php

use App\Enums\AuditAction;
use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\User;

test('a pending chapter with two admins can be approved', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->pending()->create();
    $chapter->members()->attach(User::factory(2)->create(), ['role' => ChapterMemberRole::Admin]);

    $response = $this->actingAs($admin)->post(route('admin.chapters.approval.store', $chapter));

    $response->assertSessionHasNoErrors()->assertRedirect();
    expect($chapter->fresh()->status)->toBe(ChapterStatus::Active);
    $this->assertDatabaseHas('audit_logs', [
        'actor_id' => $admin->id,
        'action' => AuditAction::ChapterApproved->value,
        'subject_type' => 'chapter',
        'subject_id' => $chapter->id,
        'old_status' => 'pending',
        'new_status' => 'active',
    ]);
});

test('a pending chapter with fewer than two admins cannot be approved', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->pending()->create();
    $chapter->members()->attach(User::factory()->create(), ['role' => ChapterMemberRole::Admin]);
    $chapter->members()->attach(User::factory()->create(), ['role' => ChapterMemberRole::Member]);

    $response = $this->actingAs($admin)->post(route('admin.chapters.approval.store', $chapter));

    $response->assertSessionHasErrors([
        'chapter' => 'A chapter needs at least 2 admins before it can be approved. This chapter has 1.',
    ]);
    expect($chapter->fresh()->status)->toBe(ChapterStatus::Pending);
    expect(AuditLog::query()->count())->toBe(0);
});

test('only pending chapters can be approved', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->create(['status' => ChapterStatus::Inactive]);
    $chapter->members()->attach(User::factory(2)->create(), ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.chapters.approval.store', $chapter))
        ->assertSessionHasErrors(['chapter' => 'Only pending chapters can be approved.']);

    expect($chapter->fresh()->status)->toBe(ChapterStatus::Inactive);
});

test('an active chapter can be deactivated with a logged reason', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->active()->create();

    $this->actingAs($admin)
        ->post(route('admin.chapters.deactivation.store', $chapter), ['reason' => 'Organisers stepped down.'])
        ->assertSessionHasNoErrors();

    expect($chapter->fresh()->status)->toBe(ChapterStatus::Inactive);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::ChapterDeactivated->value,
        'subject_id' => $chapter->id,
        'old_status' => 'active',
        'new_status' => 'inactive',
        'reason' => 'Organisers stepped down.',
    ]);
});

test('deactivating a chapter requires a reason', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->active()->create();

    $this->actingAs($admin)
        ->post(route('admin.chapters.deactivation.store', $chapter), ['reason' => ''])
        ->assertSessionHasErrors(['reason' => 'The reason field is required.']);

    expect($chapter->fresh()->status)->toBe(ChapterStatus::Active);
});

test('only active chapters can be deactivated', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.chapters.deactivation.store', $chapter), ['reason' => 'Spam.'])
        ->assertSessionHasErrors(['chapter' => 'Only active chapters can be deactivated.']);

    expect($chapter->fresh()->status)->toBe(ChapterStatus::Pending);
});

test('adding a chapter admin promotes an existing member', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->create();
    $member = User::factory()->create();
    $chapter->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->actingAs($admin)
        ->post(route('admin.chapters.admins.store', $chapter), ['email' => $member->email])
        ->assertSessionHasNoErrors();

    expect($chapter->members()->sole()->pivot->role)->toBe(ChapterMemberRole::Admin);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::ChapterAdminAdded->value,
        'subject_id' => $chapter->id,
        'old_status' => 'member',
        'new_status' => 'admin',
    ]);
});

test('adding a chapter admin adds a user who is not yet a member', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.chapters.admins.store', $chapter), ['email' => $user->email])
        ->assertSessionHasNoErrors();

    expect($chapter->members()->sole())
        ->id->toBe($user->id)
        ->pivot->role->toBe(ChapterMemberRole::Admin);
});

test('adding a chapter admin rejects an unknown email', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.chapters.admins.store', $chapter), ['email' => 'nobody@example.com'])
        ->assertSessionHasErrors(['email' => 'No user has that email address.']);

    expect($chapter->members()->count())->toBe(0);
});

test('removing a chapter admin keeps them as a member', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->create();
    $chapterAdmin = User::factory()->create();
    $chapter->members()->attach($chapterAdmin, ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($admin)
        ->delete(route('admin.chapters.admins.destroy', [$chapter, $chapterAdmin]))
        ->assertSessionHasNoErrors();

    expect($chapter->members()->sole()->pivot->role)->toBe(ChapterMemberRole::Member);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::ChapterAdminRemoved->value,
        'subject_id' => $chapter->id,
    ]);
});

test('removing an admin who is not a chapter admin returns 404', function () {
    $admin = User::factory()->superAdmin()->create();
    $chapter = Chapter::factory()->create();
    $member = User::factory()->create();
    $chapter->members()->attach($member, ['role' => ChapterMemberRole::Member]);

    $this->actingAs($admin)
        ->delete(route('admin.chapters.admins.destroy', [$chapter, $member]))
        ->assertNotFound();
});
