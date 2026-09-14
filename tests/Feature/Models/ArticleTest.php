<?php

use App\Models\Article;

it('resolves route bindings by slug', function () {
    $article = Article::factory()->create(['slug' => 'on-the-street']);

    expect($article->resolveRouteBinding('on-the-street'))->id->toBe($article->id);
});
