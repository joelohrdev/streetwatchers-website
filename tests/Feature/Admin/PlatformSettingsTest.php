<?php

use App\Enums\AuditAction;
use App\Enums\PhotoStatus;
use App\Enums\SettingKey;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('platform settings can be updated and the change is logged', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'new_photos_require_review' => false,
            'announcement_banner' => 'Photo walk this Saturday.',
        ])
        ->assertSessionHasNoErrors();

    expect(Setting::newPhotosRequireReview())->toBeFalse()
        ->and(Setting::announcementBanner())->toBe('Photo walk this Saturday.');
    $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::SettingsUpdated->value]);
});

test('new photos wait for review by default', function () {
    $photo = Photo::factory()->create(['status' => null]);

    expect($photo->fresh()->status)->toBe(PhotoStatus::Pending);
});

test('new photos are published straight away when review is turned off', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin)->put(route('admin.settings.update'), ['new_photos_require_review' => false]);

    $photo = Photo::factory()->create(['status' => null]);

    expect($photo->fresh()->status)->toBe(PhotoStatus::Published);
});

test('the announcement banner is shared with every page', function () {
    Setting::store(SettingKey::AnnouncementBanner, 'Maintenance tonight.');

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('announcement', 'Maintenance tonight.'));
});

test('a blank announcement banner is not shown', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin)->put(route('admin.settings.update'), [
        'new_photos_require_review' => true,
        'announcement_banner' => '   ',
    ]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('announcement', null));
});
