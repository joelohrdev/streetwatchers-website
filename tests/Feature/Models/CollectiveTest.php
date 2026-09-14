<?php

use App\Models\Collective;

it('resolves route bindings by slug', function () {
    $collective = Collective::factory()->create(['slug' => 'night-owls']);

    expect($collective->resolveRouteBinding('night-owls'))->id->toBe($collective->id);
});
