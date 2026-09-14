<?php

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Correspondent;
use App\Models\User;

test('granting correspondent status creates an active correspondent', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.correspondents.store'), ['user_id' => $user->id, 'bio' => 'Covers housing in Leeds.'])
        ->assertSessionHasNoErrors();

    expect($user->fresh())
        ->role->toBe(UserRole::Correspondent)
        ->correspondent->is_active->toBeTrue()
        ->correspondent->bio->toBe('Covers housing in Leeds.');
    $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::CorrespondentGranted->value]);
});

test('granting correspondent status reactivates an earlier correspondent record', function () {
    $admin = User::factory()->superAdmin()->create();
    $correspondent = Correspondent::factory()->inactive()->create();

    $this->actingAs($admin)
        ->post(route('admin.correspondents.store'), ['user_id' => $correspondent->user_id, 'bio' => 'Back on the beat.'])
        ->assertSessionHasNoErrors();

    expect(Correspondent::query()->sole())
        ->id->toBe($correspondent->id)
        ->is_active->toBeTrue();
});

test('granting correspondent status to an active correspondent is rejected', function () {
    $admin = User::factory()->superAdmin()->create();
    $correspondent = Correspondent::factory()->active()->create();

    $this->actingAs($admin)
        ->post(route('admin.correspondents.store'), ['user_id' => $correspondent->user_id, 'bio' => 'Again.'])
        ->assertSessionHasErrors(['user_id' => 'This user is already an active correspondent.']);
});

test('granting correspondent status keeps a chapter admin role', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['role' => UserRole::ChapterAdmin]);

    $this->actingAs($admin)
        ->post(route('admin.correspondents.store'), ['user_id' => $user->id, 'bio' => 'Bio.'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->role)->toBe(UserRole::ChapterAdmin);
});

test('revoking correspondent status deactivates the record without deleting it', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['role' => UserRole::Correspondent]);
    $correspondent = Correspondent::factory()->active()->for($user)->create();

    $this->actingAs($admin)
        ->delete(route('admin.correspondents.destroy', $correspondent), ['reason' => 'Fabricated sources.'])
        ->assertSessionHasNoErrors();

    expect($correspondent->fresh())->not->toBeNull()
        ->is_active->toBeFalse()
        ->and($user->fresh()->role)->toBe(UserRole::Member);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::CorrespondentRevoked->value,
        'reason' => 'Fabricated sources.',
    ]);
});

test('any published article can be unpublished with a reason', function () {
    $admin = User::factory()->superAdmin()->create();
    $article = Article::factory()->published()->create();

    $this->actingAs($admin)
        ->delete(route('admin.articles.publication.destroy', $article), ['reason' => 'Defamatory claims.'])
        ->assertSessionHasNoErrors();

    expect($article->fresh()->published_at)->toBeNull();
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::ArticleUnpublished->value,
        'subject_type' => 'article',
        'subject_id' => $article->id,
        'reason' => 'Defamatory claims.',
    ]);
});

test('unpublishing a draft article is rejected', function () {
    $admin = User::factory()->superAdmin()->create();
    $article = Article::factory()->draft()->create();

    $this->actingAs($admin)
        ->delete(route('admin.articles.publication.destroy', $article), ['reason' => 'Whatever.'])
        ->assertSessionHasErrors(['article' => 'This article is not published.']);
});
