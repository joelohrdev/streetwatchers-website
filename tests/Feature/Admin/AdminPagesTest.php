<?php

use App\Enums\ChapterStatus;
use App\Enums\PhotoStatus;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\Report;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('super admins can open every admin page', function () {
    $admin = User::factory()->superAdmin()->create();
    $pages = collect(adminRequests())->where('method', 'GET');

    $failing = $pages
        ->reject(fn (array $page): bool => $this->actingAs($admin)->get($page['url'])->isOk())
        ->pluck('name');

    expect($pages)->not->toBeEmpty()
        ->and($failing->all())->toBe([]);
});

test('the dashboard counts chapters and photos by status', function () {
    $admin = User::factory()->superAdmin()->create();
    Chapter::factory(2)->active()->create();
    Chapter::factory()->pending()->create();
    Photo::factory(3)->published()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Dashboard')
            ->where('stats.chapters', collect(ChapterStatus::cases())->map(fn (ChapterStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'count' => ['pending' => 1, 'active' => 2, 'inactive' => 0][$status->value],
            ])->all())
            ->where('stats.photos.1', ['value' => 'published', 'label' => PhotoStatus::Published->label(), 'count' => 3])
            ->where('stats.users', 4));
});

test('the chapter list can be filtered by status', function () {
    $admin = User::factory()->superAdmin()->create();
    $pending = Chapter::factory()->pending()->create();
    Chapter::factory()->active()->create();

    $this->actingAs($admin)
        ->get(route('admin.chapters.index', ['status' => 'pending']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/chapters/Index')
            ->has('chapters.data', 1)
            ->where('chapters.data.0.id', $pending->id)
            ->where('filters.status', 'pending'));
});

test('the report list can be filtered by status and reported content type', function () {
    $admin = User::factory()->superAdmin()->create();
    $openPhotoReport = Report::factory()->open()->for(Photo::factory(), 'reportable')->create();
    Report::factory()->open()->for(Comment::factory(), 'reportable')->create();
    Report::factory()->for(Photo::factory(), 'reportable')->create(['status' => 'dismissed']);

    $this->actingAs($admin)
        ->get(route('admin.reports.index', ['status' => 'open', 'type' => 'photo']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/reports/Index')
            ->has('reports.data', 1)
            ->where('reports.data.0.id', $openPhotoReport->id)
            ->where('reports.data.0.reportable.type_label', 'Photo'));
});

test('the user list can be searched by name or email', function () {
    $admin = User::factory()->superAdmin()->create(['name' => 'Admin']);
    $match = User::factory()->create(['name' => 'Vivian Maier', 'email' => 'vivian@example.com']);
    User::factory()->create(['name' => 'Garry Winogrand', 'email' => 'garry@example.com']);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'vivian']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/Index')
            ->has('users.data', 1)
            ->where('users.data.0.id', $match->id));
});
