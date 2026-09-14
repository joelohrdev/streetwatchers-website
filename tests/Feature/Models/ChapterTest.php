<?php

use App\Models\Chapter;

it('resolves route bindings by slug', function () {
    $chapter = Chapter::factory()->create(['slug' => 'london']);

    expect($chapter->resolveRouteBinding('london'))->id->toBe($chapter->id);
});
