<?php

use App\Enums\CollectiveMemberRole;
use App\Models\Collective;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function collectiveDetails(array $overrides = []): array
{
    return [
        'name' => 'Night Shift',
        'description' => 'We photograph cities after dark, from late trains to the last bus home.',
        'based_in' => 'Chicago, United States',
        'website_url' => 'https://nightshift.example',
        'instagram_url' => 'https://instagram.com/nightshift',
        'is_open_for_applications' => '1',
        ...$overrides,
    ];
}

test('guests are redirected to login from the start a collective page', function () {
    $this->get(route('collectives.create'))->assertRedirect(route('login'));
});

test('members can open the start a collective page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('collectives.create'))
        ->assertInertia(fn (Assert $page) => $page->component('collectives/Create'));
});

test('a member can start a collective and becomes its founder', function () {
    Storage::fake('public');
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('collectives.store'), collectiveDetails([
            'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
        ]))
        ->assertRedirect(route('collectives.show', 'night-shift'))
        ->assertSessionHas('status', 'collective-created');

    $collective = Collective::query()->sole();

    expect($collective)
        ->slug->toBe('night-shift')
        ->based_in->toBe('Chicago, United States')
        ->is_open_for_applications->toBeTrue()
        ->is_verified->toBeFalse()
        ->and($collective->roleOf($member))->toBe(CollectiveMemberRole::Founder);
    Storage::disk('public')->assertExists($collective->logo_path);
});

test('starting a collective rejects invalid details', function (array $overrides, string $field, string $message) {
    $this->actingAs(User::factory()->create())
        ->post(route('collectives.store'), collectiveDetails($overrides))
        ->assertSessionHasErrors([$field => $message]);

    expect(Collective::query()->count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name', 'The name field is required.'],
    'short description' => [['description' => 'Night photos.'], 'description', 'Tell people a little more about what the collective does.'],
    'instagram link elsewhere' => [['instagram_url' => 'https://example.com/nightshift'], 'instagram_url', 'Use a link to an Instagram profile, such as https://instagram.com/yourcollective.'],
]);

test('a collective name must be unique among collectives still on the platform', function () {
    Collective::factory()->create(['name' => 'Night Shift']);

    $this->actingAs(User::factory()->create())
        ->post(route('collectives.store'), collectiveDetails())
        ->assertSessionHasErrors(['name' => 'A collective with this name already exists.']);
});

test('the name of a removed collective can be reused with a new slug', function () {
    Collective::factory()->create(['name' => 'Night Shift', 'slug' => 'night-shift'])->delete();

    $this->actingAs(User::factory()->create())
        ->post(route('collectives.store'), collectiveDetails())
        ->assertSessionHasNoErrors();

    expect(Collective::query()->sole()->slug)->toBe('night-shift-2');
});

test('a founder can edit their collective and remove its logo', function () {
    Storage::fake('public');
    $founder = User::factory()->create();
    $collective = Collective::factory()->create(['name' => 'Night Shift', 'slug' => 'night-shift', 'logo_path' => UploadedFile::fake()->image('old.png')->store('collective-logos', 'public')]);
    $collective->members()->attach($founder, ['role' => CollectiveMemberRole::Founder]);
    $oldLogo = $collective->logo_path;

    $this->actingAs($founder)
        ->put(route('collectives.update', $collective), collectiveDetails([
            'description' => 'Now photographing night workers across the whole city.',
            'is_open_for_applications' => '0',
            'remove_logo' => '1',
        ]))
        ->assertRedirect(route('collectives.show', 'night-shift'));

    expect($collective->fresh())
        ->description->toBe('Now photographing night workers across the whole city.')
        ->is_open_for_applications->toBeFalse()
        ->logo_path->toBeNull();
    Storage::disk('public')->assertMissing($oldLogo);
});

test('only founders can edit a collective', function () {
    $collective = Collective::factory()->create();
    $member = User::factory()->create();
    $collective->members()->attach($member, ['role' => CollectiveMemberRole::Member]);

    $this->actingAs($member)->get(route('collectives.edit', $collective))->assertForbidden();
    $this->actingAs($member)->put(route('collectives.update', $collective), collectiveDetails())->assertForbidden();
});

test('a member can leave a collective', function () {
    $collective = Collective::factory()->create();
    $member = User::factory()->create();
    $collective->members()->attach($member, ['role' => CollectiveMemberRole::Member]);

    $this->actingAs($member)
        ->delete(route('collectives.membership.destroy', $collective))
        ->assertRedirect(route('collectives.show', $collective));

    expect($collective->roleOf($member))->toBeNull();
});

test('a founder cannot leave their collective from its page', function () {
    $collective = Collective::factory()->create();
    $founder = User::factory()->create();
    $collective->members()->attach($founder, ['role' => CollectiveMemberRole::Founder]);

    $this->actingAs($founder)
        ->delete(route('collectives.membership.destroy', $collective))
        ->assertSessionHasErrors(['membership' => 'Founders can’t leave their collective here. Get in touch and we will help hand it over.']);

    expect($collective->roleOf($founder))->toBe(CollectiveMemberRole::Founder);
});
