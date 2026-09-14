<?php

use App\Enums\AuditAction;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;

test('merging a tag moves its photos to the target tag and deletes it', function () {
    $admin = User::factory()->superAdmin()->create();
    $source = Tag::factory()->create(['name' => 'nightime']);
    $target = Tag::factory()->create(['name' => 'night']);
    $onlySource = Photo::factory()->create();
    $both = Photo::factory()->create();
    $source->photos()->attach([$onlySource->id, $both->id]);
    $target->photos()->attach($both);

    $this->actingAs($admin)
        ->post(route('admin.tags.merge.store', $source), ['target_tag_id' => $target->id])
        ->assertSessionHasNoErrors();

    $this->assertModelMissing($source);
    expect($target->photos()->pluck('photos.id')->sort()->values()->all())
        ->toBe(collect([$onlySource->id, $both->id])->sort()->values()->all());
    $this->assertDatabaseHas('audit_logs', [
        'action' => AuditAction::TagMerged->value,
        'subject_type' => 'tag',
        'subject_id' => $target->id,
    ]);
});

test('a tag cannot be merged into itself', function () {
    $admin = User::factory()->superAdmin()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.tags.merge.store', $tag), ['target_tag_id' => $tag->id])
        ->assertSessionHasErrors(['target_tag_id' => 'A tag cannot be merged into itself.']);

    $this->assertModelExists($tag);
});

test('an unused tag can be deleted', function () {
    $admin = User::factory()->superAdmin()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.tags.destroy', $tag))
        ->assertSessionHasNoErrors();

    $this->assertModelMissing($tag);
    $this->assertDatabaseHas('audit_logs', ['action' => AuditAction::TagDeleted->value]);
});

test('a tag used by photos cannot be deleted', function () {
    $admin = User::factory()->superAdmin()->create();
    $tag = Tag::factory()->create();
    $tag->photos()->attach(Photo::factory()->create());

    $this->actingAs($admin)
        ->delete(route('admin.tags.destroy', $tag))
        ->assertSessionHasErrors(['tag' => 'This tag is still used by photos. Merge it into another tag instead.']);

    $this->assertModelExists($tag);
});
