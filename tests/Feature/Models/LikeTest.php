<?php

use App\Models\Like;
use Illuminate\Database\UniqueConstraintViolationException;

it('rejects a second like of the same photo by the same user', function () {
    $like = Like::factory()->create();

    Like::factory()->create(['photo_id' => $like->photo_id, 'user_id' => $like->user_id]);
})->throws(UniqueConstraintViolationException::class);
