<?php

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Enums\Country;
use App\Enums\SettingKey;
use App\Models\Chapter;
use App\Models\Setting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function chapterProposal(array $overrides = []): array
{
    return [
        'name' => 'Glasgow Streetwatchers',
        'city' => 'Glasgow',
        'country' => 'GB',
        'latitude' => '55.8642',
        'longitude' => '-4.2518',
        'description' => 'Weekly walks along the Clyde and through the West End.',
        ...$overrides,
    ];
}

test('guests are redirected to login from the start a chapter page', function () {
    $this->get(route('chapters.create'))->assertRedirect(route('login'));
});

test('members can open the start a chapter page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('chapters.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('chapters/Create')
            ->where('submittedChapter', null)
            ->where('countries.0', ['value' => 'AF', 'label' => 'Afghanistan'])
            ->has('countries', count(Country::cases())));
});

test('a member can propose a chapter and becomes its first admin', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('chapters.store'), chapterProposal())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('chapters.create'))
        ->assertSessionHas('submitted_chapter', 'Glasgow Streetwatchers');

    $chapter = Chapter::query()->sole();

    expect($chapter)
        ->name->toBe('Glasgow Streetwatchers')
        ->slug->toBe('glasgow-streetwatchers')
        ->status->toBe(ChapterStatus::Pending)
        ->country->toBe(Country::UnitedKingdom)
        ->latitude->toBe(55.8642)
        ->longitude->toBe(-4.2518)
        ->and($chapter->members()->sole())
        ->id->toBe($member->id)
        ->pivot->role->toBe(ChapterMemberRole::Admin);
});

test('the confirmation is shown after proposing a chapter', function () {
    $this->actingAs(User::factory()->create())
        ->followingRedirects()
        ->post(route('chapters.store'), chapterProposal())
        ->assertInertia(fn (Assert $page) => $page->where('submittedChapter', 'Glasgow Streetwatchers'));
});

test('the slug gets a number when another chapter already uses it', function () {
    Chapter::factory()->create(['name' => 'Glasgow Walkers', 'slug' => 'glasgow-streetwatchers']);

    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), chapterProposal())
        ->assertSessionHasNoErrors();

    expect(Chapter::query()->where('name', 'Glasgow Streetwatchers')->sole()->slug)->toBe('glasgow-streetwatchers-2');
});

test('proposing a chapter requires its details', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), [])
        ->assertSessionHasErrors([
            'name' => 'The name field is required.',
            'city' => 'The city field is required.',
            'country' => 'The country field is required.',
            'latitude' => 'The latitude field is required.',
            'longitude' => 'The longitude field is required.',
            'description' => 'The description field is required.',
        ]);

    expect(Chapter::query()->count())->toBe(0);
});

test('proposing a chapter rejects invalid details', function (array $overrides, string $field, string $message) {
    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), chapterProposal($overrides))
        ->assertSessionHasErrors([$field => $message]);

    expect(Chapter::query()->count())->toBe(0);
})->with([
    'latitude out of range' => [['latitude' => '91'], 'latitude', 'The latitude field must be between -90 and 90.'],
    'longitude out of range' => [['longitude' => '-181'], 'longitude', 'The longitude field must be between -180 and 180.'],
    'country written out instead of chosen' => [['country' => 'United Kingdom'], 'country', 'The selected country is invalid.'],
]);

test('a chapter name must be unique', function () {
    Chapter::factory()->create(['name' => 'Glasgow Streetwatchers']);

    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), chapterProposal())
        ->assertSessionHasErrors(['name' => 'A group with this name already exists.']);
});

test('a member with a chapter awaiting approval cannot propose another', function () {
    $member = User::factory()->create();
    Chapter::factory()->pending()->create()->members()->attach($member, ['role' => ChapterMemberRole::Admin]);

    $this->actingAs($member)
        ->post(route('chapters.store'), chapterProposal())
        ->assertSessionHasErrors(['name' => 'You already have a group waiting for approval.']);

    expect(Chapter::query()->count())->toBe(1);
});

test('a chapter named after a reserved path gets a numbered slug', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), chapterProposal(['name' => 'Create']))
        ->assertSessionHasNoErrors();

    expect(Chapter::query()->sole()->slug)->toBe('create-2');
});

test('a chapter cannot be proposed within the group radius of an active chapter', function () {
    // Paisley is about 7 miles from the proposed Glasgow group.
    Chapter::factory()->active()->create(['name' => 'Paisley Streetwatchers', 'latitude' => 55.8456, 'longitude' => -4.4239]);

    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), chapterProposal())
        ->assertSessionHasErrors([
            'latitude' => 'Paisley Streetwatchers is 7 miles from here. Groups must be at least 25 miles apart, so join that group instead.',
        ]);

    expect(Chapter::query()->count())->toBe(1);
});

test('a chapter cannot be proposed within the group radius of a pending chapter', function () {
    Chapter::factory()->pending()->create(['latitude' => 55.8456, 'longitude' => -4.4239]);

    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), chapterProposal())
        ->assertSessionHasErrors([
            'latitude' => 'A group is already waiting for approval 7 miles from here. Groups must be at least 25 miles apart.',
        ]);
});

test('a chapter can be proposed near an inactive chapter or beyond the group radius', function () {
    Chapter::factory()->create(['status' => ChapterStatus::Inactive, 'latitude' => 55.8456, 'longitude' => -4.4239]);
    // Edinburgh is about 42 miles from Glasgow.
    Chapter::factory()->active()->create(['latitude' => 55.9533, 'longitude' => -3.1883]);

    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), chapterProposal())
        ->assertSessionHasNoErrors();

    expect(Chapter::query()->where('name', 'Glasgow Streetwatchers')->exists())->toBeTrue();
});

test('the group radius comes from the platform settings', function () {
    Setting::store(SettingKey::GroupRadiusMiles, '50');
    Chapter::factory()->active()->create(['name' => 'Edinburgh Streetwatchers', 'latitude' => 55.9533, 'longitude' => -3.1883]);

    $this->actingAs(User::factory()->create())
        ->post(route('chapters.store'), chapterProposal())
        ->assertSessionHasErrors([
            'latitude' => 'Edinburgh Streetwatchers is 42 miles from here. Groups must be at least 50 miles apart, so join that group instead.',
        ]);
});
