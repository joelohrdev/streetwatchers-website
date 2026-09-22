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
            'group_radius_miles' => 40,
        ])
        ->assertSessionHasNoErrors();

    expect(Setting::newPhotosRequireReview())->toBeFalse()
        ->and(Setting::announcementBanner())->toBe('Photo walk this Saturday.')
        ->and(Setting::groupRadiusInMiles())->toBe(40);
    $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::SettingsUpdated->value]);
});

test('new photos wait for review by default', function () {
    $photo = Photo::factory()->create(['status' => null]);

    expect($photo->fresh()->status)->toBe(PhotoStatus::Pending);
});

test('new photos are published straight away when review is turned off', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin)->put(route('admin.settings.update'), ['new_photos_require_review' => false, 'group_radius_miles' => 25]);

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
        'group_radius_miles' => 25,
    ]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('announcement', null));
});

test('groups must be 25 miles apart by default', function () {
    expect(Setting::groupRadiusInMiles())->toBe(25);
});

test('the group spacing must be a whole number of miles in range', function (mixed $radius) {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->put(route('admin.settings.update'), [
            'new_photos_require_review' => true,
            'group_radius_miles' => $radius,
        ])
        ->assertSessionHasErrors('group_radius_miles');
})->with([
    'missing' => [null],
    'zero' => [0],
    'too large' => [251],
    'fractional' => [12.5],
]);
