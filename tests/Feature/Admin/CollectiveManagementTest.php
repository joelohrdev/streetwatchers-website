<?php

use App\Enums\AuditAction;
use App\Models\Collective;
use App\Models\User;

test('a collective can be verified', function () {
    $admin = User::factory()->superAdmin()->create();
    $collective = Collective::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.collectives.verification.store', $collective))
        ->assertSessionHasNoErrors();

    expect($collective->fresh()->is_verified)->toBeTrue();
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::CollectiveVerified->value,
        'subject_type' => 'collective',
        'subject_id' => $collective->id,
    ]);
});

test('a verified collective can be unverified', function () {
    $admin = User::factory()->superAdmin()->create();
    $collective = Collective::factory()->verified()->create();

    $this->actingAs($admin)
        ->delete(route('admin.collectives.verification.destroy', $collective))
        ->assertSessionHasNoErrors();

    expect($collective->fresh()->is_verified)->toBeFalse();
    $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::CollectiveUnverified->value]);
});

test('a collective can be removed from the platform with a reason', function () {
    $admin = User::factory()->superAdmin()->create();
    $collective = Collective::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.collectives.destroy', $collective), ['reason' => 'Impersonating another group.'])
        ->assertSessionHasNoErrors();

    $this->assertSoftDeleted($collective);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::CollectiveRemoved->value,
        'subject_id' => $collective->id,
        'reason' => 'Impersonating another group.',
    ]);
});

test('removing a collective requires a reason', function () {
    $admin = User::factory()->superAdmin()->create();
    $collective = Collective::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.collectives.destroy', $collective))
        ->assertSessionHasErrors(['reason' => 'The reason field is required.']);

    $this->assertNotSoftDeleted($collective);
});
