<?php

use App\Enums\AuditAction;
use App\Enums\PhotoStatus;
use App\Enums\ReportStatus;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\Report;
use App\Models\User;

test('a report status can be updated and is logged', function () {
    $admin = User::factory()->superAdmin()->create();
    $report = Report::factory()->open()->create();

    $this->actingAs($admin)
        ->patch(route('admin.reports.update', $report), ['status' => 'dismissed', 'reason' => 'Not a violation.'])
        ->assertSessionHasNoErrors();

    expect($report->fresh()->status)->toBe(ReportStatus::Dismissed);
    $this->assertDatabaseHas('audit_logs', [
        'actor_id' => $admin->id,
        'action' => AuditAction::ReportStatusUpdated->value,
        'subject_type' => 'report',
        'subject_id' => $report->id,
        'old_status' => 'open',
        'new_status' => 'dismissed',
        'reason' => 'Not a violation.',
    ]);
});

test('updating a report rejects an unknown status', function () {
    $admin = User::factory()->superAdmin()->create();
    $report = Report::factory()->open()->create();

    $this->actingAs($admin)
        ->patch(route('admin.reports.update', $report), ['status' => 'deleted'])
        ->assertSessionHasErrors(['status' => 'The selected status is invalid.']);

    expect($report->fresh()->status)->toBe(ReportStatus::Open);
});

test('a reported photo can be removed from the report, which marks the report actioned', function () {
    $admin = User::factory()->superAdmin()->create();
    $photo = Photo::factory()->published()->create();
    $report = Report::factory()->open()->for($photo, 'reportable')->create();

    $this->actingAs($admin)
        ->put(route('admin.reports.photo-status.update', $report), ['status' => 'removed', 'reason' => 'Identifiable minor.'])
        ->assertSessionHasNoErrors();

    expect($photo->fresh()->status)->toBe(PhotoStatus::Removed)
        ->and($report->fresh()->status)->toBe(ReportStatus::Actioned);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::PhotoStatusUpdated->value,
        'subject_type' => 'photo',
        'subject_id' => $photo->id,
        'old_status' => 'published',
        'new_status' => 'removed',
        'reason' => 'Identifiable minor.',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::ReportStatusUpdated->value,
        'subject_type' => 'report',
        'subject_id' => $report->id,
        'new_status' => 'actioned',
    ]);
});

test('a photo can only be flagged or removed from a report', function () {
    $admin = User::factory()->superAdmin()->create();
    $photo = Photo::factory()->published()->create();
    $report = Report::factory()->for($photo, 'reportable')->create();

    $this->actingAs($admin)
        ->put(route('admin.reports.photo-status.update', $report), ['status' => 'published', 'reason' => 'Looks fine.'])
        ->assertSessionHasErrors(['status' => 'The selected status is invalid.']);

    expect($photo->fresh()->status)->toBe(PhotoStatus::Published);
});

test('changing a photo status from a report requires a reason', function () {
    $admin = User::factory()->superAdmin()->create();
    $report = Report::factory()->for(Photo::factory()->published(), 'reportable')->create();

    $this->actingAs($admin)
        ->put(route('admin.reports.photo-status.update', $report), ['status' => 'flagged'])
        ->assertSessionHasErrors(['reason' => 'The reason field is required.']);
});

test('photo status cannot be changed from a report about a comment', function () {
    $admin = User::factory()->superAdmin()->create();
    $report = Report::factory()->for(Comment::factory(), 'reportable')->create();

    $this->actingAs($admin)
        ->put(route('admin.reports.photo-status.update', $report), ['status' => 'removed', 'reason' => 'Abuse.'])
        ->assertNotFound();
});
