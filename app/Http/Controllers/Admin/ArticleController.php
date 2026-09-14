<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $publication = in_array($request->string('publication')->toString(), ['published', 'draft'], true)
            ? $request->string('publication')->toString()
            : null;

        $articles = Article::query()
            ->with('user')
            ->when($search !== '', fn (Builder $query) => $query->whereLike('title', "%{$search}%"))
            ->when($publication === 'published', fn (Builder $query) => $query->whereNotNull('published_at'))
            ->when($publication === 'draft', fn (Builder $query) => $query->whereNull('published_at'))
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Article $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'author' => $article->user->name,
                'published_at' => $article->published_at?->toIso8601String(),
                'created_at' => $article->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/articles/Index', [
            'articles' => $articles,
            'filters' => ['search' => $search, 'publication' => $publication],
        ]);
    }
}
