<?php

use App\Models\User;

it('records only the creation time when a user follows another user', function () {
    $this->freezeSecond();
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $follower->following()->attach($followed);

    expect($followed->followers()->sole())
        ->id->toBe($follower->id)
        ->pivot->created_at->toDateTimeString()->toBe(now()->toDateTimeString());
});
