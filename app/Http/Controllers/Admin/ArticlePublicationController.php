<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ArticlePublicationController extends Controller
{
    /**
     * Unpublish an article regardless of who wrote it.
     */
    public function destroy(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        if ($article->published_at === null) {
            throw ValidationException::withMessages([
                'article' => 'This article is not published.',
            ]);
        }

        DB::transaction(function () use ($request, $article, $validated): void {
            $publishedAt = $article->published_at;
            $article->update(['published_at' => null]);

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::ArticleUnpublished,
                subject: $article,
                oldStatus: 'published',
                newStatus: 'draft',
                reason: $validated['reason'],
                metadata: ['published_at' => $publishedAt->toIso8601String()],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" unpublished.', ['title' => $article->title])]);

        return back();
    }
}
