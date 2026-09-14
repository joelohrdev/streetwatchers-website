<?php

use App\Enums\ReportStatus;
use App\Models\Photo;
use App\Models\Report;
use Inertia\Testing\AssertableInertia as Assert;

test('the photo removal request form is available without logging in', function () {
    $this->get(route('photo-removal-requests.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('photo-removal-requests/Create')
            ->where('submitted', false));
});

test('a guest can request removal of a photo by its ID', function () {
    $photo = Photo::factory()->create();

    $this->post(route('photo-removal-requests.store'), [
        'email' => 'subject@example.com',
        'photo_reference' => (string) $photo->id,
        'reason' => 'I am in this photo and did not consent.',
    ])->assertRedirect(route('photo-removal-requests.create'));

    expect(Report::query()->sole())
        ->reportable_type->toBe('photo')
        ->reportable_id->toBe($photo->id)
        ->reporter_id->toBeNull()
        ->reporter_email->toBe('subject@example.com')
        ->is_public_submission->toBeTrue()
        ->status->toBe(ReportStatus::Open)
        ->reason->toBe('I am in this photo and did not consent.');
    $this->assertGuest();
});

test('a guest can request removal of a photo by its link', function () {
    $photo = Photo::factory()->create();

    $this->post(route('photo-removal-requests.store'), [
        'email' => 'subject@example.com',
        'photo_reference' => "https://streetwatchers.test/photos/{$photo->id}/",
        'reason' => 'Please remove this.',
    ])->assertSessionHasNoErrors();

    expect(Report::query()->sole()->reportable_id)->toBe($photo->id);
});

test('the form confirms the request after it is submitted', function () {
    $photo = Photo::factory()->create();

    $this->followingRedirects()
        ->post(route('photo-removal-requests.store'), [
            'email' => 'subject@example.com',
            'photo_reference' => (string) $photo->id,
            'reason' => 'Please remove this.',
        ])
        ->assertInertia(fn (Assert $page) => $page->where('submitted', true));
});

test('a removal request for a photo that does not exist is rejected', function () {
    $this->post(route('photo-removal-requests.store'), [
        'email' => 'subject@example.com',
        'photo_reference' => 'https://streetwatchers.test/photos/999999',
        'reason' => 'Please remove this.',
    ])->assertSessionHasErrors(['photo_reference' => 'We could not find a photo matching that ID or link.']);

    expect(Report::query()->count())->toBe(0);
});

test('a removal request requires an email, a photo reference and a reason', function () {
    $this->post(route('photo-removal-requests.store'), [])
        ->assertSessionHasErrors([
            'email' => 'The email field is required.',
            'photo_reference' => 'The photo reference field is required.',
            'reason' => 'The reason field is required.',
        ]);
});
